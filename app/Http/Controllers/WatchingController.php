<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\CourseResource;


use Carbon\Carbon;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Exception;
use Google_Client;
use DateInterval;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;


use App\Models\Course;
use App\Models\RatingCourse;
use App\Models\SavedCourse;

use App\Models\User;
use App\Models\TutorCourse;
use App\Models\Registration;

use App\Models\Difficulty;
use App\Models\Career;
use App\Models\Category;

use App\Models\module;
use App\Models\Question;
use App\Models\TypeQuestion;
use App\Models\Answer;

use App\Models\Chapter;
use App\Models\Test;
use App\Models\ContentChapter;
use App\Models\LikeChapter;
use App\Models\CompletedChapter;

use App\Models\LearningContent;
use App\Models\ContentView;
use App\Models\TypeLearningContent;
use App\Models\Format;

class WatchingController extends Controller
{
     use AuthorizesRequests;
    public function showCourse(Course $course)
    {
        
        $this->authorize('view', $course);
        $userId = auth()->id();
        
        // 2) Eager load para navegación (módulos → capítulos) + métricas
        $course->load([
            'modules' => function ($q) use ($userId) {
                $q->orderBy('order')
                  ->select('id', 'name', 'order', 'course_id', 'updated_at', 'created_at')
                  ->with([
                      'chapters' => function ($cq) use ($userId) {
                          $cq->orderBy('order')
                             ->select('id', 'title', 'order', 'module_id', 'updated_at', 'created_at')
                             ->withCount('questions')
                             ->withMax('questions', 'updated_at') // alias: questions_max_updated_at
                             ->with([
                                'test:id,chapter_id,split',
                                'learningContent' => function ($lq) {
                                     // Seleccionamos size_mb, duration_seconds y format_id
                                    $lq->select('id', 'chapter_id', 'url', 'type_content_id', 'format_id', 'size_bytes', 'duration_seconds', 'updated_at')
                                    ->with([
                                        'typeLearningContent:id,name',
                                        'format:id,name' // Cargamos la relación del formato
                                    ]);
                                },
                                'completedChapters' => function ($ccq) use ($userId) {
                                    if ($userId) {
                                    $ccq->select('id', 'chapter_id', 'user_id', 'created_at')
                                    ->where('user_id', $userId)
                                    ->orderByDesc('created_at'); // el más reciente primero
                                    } else {
                                    $ccq->whereRaw('1=0');
                                    }
                                },
                             ]);
                      }
                  ]);
            },
        ]);

        // 3) Flag: usuario registrado al curso
        if ($userId) {
            $course->loadCount([
                'registrations as is_registered' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ]);
        }

        // 4) Resolver "último capítulo visto" según tu regla
        $lastViewed = $this->resolveLastViewedChapter($course, $userId);

        // 5) Respuesta
        return response()->json([
            'ok'     => true,
            'course' => [
                'id'            => $course->id,
                'title'         => $course->title,
                'updated_at'    => $course->updated_at,
                'created_at'    => $course->created_at,
                'is_registered' => $userId ? (bool) ($course->is_registered ?? 0) : false,

                // << NUEVO: último capítulo visto >>
                'last_viewed_chapter' => $lastViewed,

                'modules'       => $course->modules->map(function ($m) use ($userId) {
                    return [
                        'id'         => $m->id,
                        'name'       => $m->name,
                        'order'      => $m->order,
                        'updated_at' => $m->updated_at,
                        'created_at' => $m->created_at,
                        'chapters'   => $m->chapters->map(function ($c) use ($userId) {
                            $questionsCount = (int) ($c->questions_count ?? 0);

                            // split >= 1; si viene null/0/false => 1
                            $splitFactor = max(1, (int) optional($c->test)->split);

                            // entero por división (piso)
                            $questionsPerSplit = intdiv($questionsCount, $splitFactor);

                            return [
                                'id'              => $c->id,
                                'title'           => $c->title,
                                'order'           => $c->order,
                                'updated_at'      => $c->updated_at,
                                'created_at'      => $c->created_at,
                                'questions_count' => $questionsPerSplit,

                                // tipos/format del learning (sin exponer URL)
                                'learning'        => $this->formatLearningMeta(optional($c->learningContent)),

                                // estado de completado (regla avanzada)
                                'completed_chapter' => $userId
                                    ? $this->evaluateChapterCompletion($c)
                                    : null,
                            ];
                        })->values(),
                    ];
                })->values(),
            ],
        ], 200);
    }

    private function resolveLastViewedChapter(Course $course, ?int $userId): ?array
    {
        // Fallback: primer capítulo por orden (primer módulo, primer capítulo)
        $firstChapter = optional(
            $course->modules->sortBy('order')->values()->first()
        )->chapters->sortBy('order')->values()->first();

        // Si no hay usuario o no hay capítulos, devolvemos fallback (o null si ni eso existe)
        if (!$userId) {
            if (!$firstChapter) return null;
            return [
                'chapter_id'    => $firstChapter->id,
                'chapter_title' => $firstChapter->title,
                'content_view'  => null,
            ];
        }

        // Buscar historial del usuario (limitamos a 50 para iterar rápido)
        $views = ContentView::with([
                'learningContent:id,chapter_id,updated_at',
                'learningContent.chapter:id,title,module_id'
            ])
            ->where('user_id', $userId)
            ->whereHas('learningContent.chapter.module', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->orderByDesc('updated_at') // si no tienes timestamps en la tabla, cambia por created_at
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        foreach ($views as $cv) {
            $lc = $cv->learningContent;
            if (!$lc || !$lc->chapter) continue;

            // Fecha de "visto" (preferimos updated_at, fallback a created_at)
            $seenAt = $cv->updated_at ? Carbon::parse($cv->updated_at)
                                      : ($cv->created_at ? Carbon::parse($cv->created_at) : null);
            if (!$seenAt) continue;

            // Regla de invalidez: si learning fue actualizado después de la vista, la vista NO es válida
            $learningUpdatedAt = $lc->updated_at ? Carbon::parse($lc->updated_at) : null;

            if ($learningUpdatedAt instanceof Carbon && !$seenAt->gt($learningUpdatedAt)) {
                // no válida → probar con la siguiente más antigua
                continue;
            }

            // Válida: devolver este capítulo
            return [
                'chapter_id'    => $lc->chapter->id,
                'chapter_title' => $lc->chapter->title,
                'content_view'  => [
                    'learning_content_id' => $lc->id,
                    'seen_at'             => $seenAt->toISOString(),
                    'second_seen'         => $cv->second_seen,
                ],
            ];
        }

        // No hubo historial válido → fallback al primer capítulo
        if ($firstChapter) {
            return [
                'chapter_id'    => $firstChapter->id,
                'chapter_title' => $firstChapter->title,
                'content_view'  => null,
            ];
        }

        // No hay capítulos en el curso
        return null;
    }
    private function evaluateChapterCompletion($chapter): array
{
    // Si no está cargada la relación, no podemos evaluar
    if (!$chapter->relationLoaded('completedChapters')) {
        return [
            'is_completed'  => false,
            'completed_at'  => null,
            'completion_id' => null,
        ];
    }

    // Tomar el registro MÁS RECIENTE de completed_chapters para este usuario
    $latest = $chapter->completedChapters
        ->sortByDesc(function ($r) {
            return $r->created_at ? \Carbon\Carbon::parse($r->created_at)->timestamp : 0;
        })
        ->first();

    if (!$latest) {
        return [
            'is_completed'  => false,
            'completed_at'  => null,
            'completion_id' => null,
        ];
    }

    return [
        'is_completed'  => true,
        'completed_at'  => \Carbon\Carbon::parse($latest->created_at)->toIso8601String(),
        'completion_id' => $latest->id,
    ];
}

/**
 * Convierte los bytes en una cadena legible al estilo Windows (sin depender de intl).
 */
private function formatFileSize(?int $bytes): ?string
{
    if ($bytes === null || $bytes === 0) {
        return null;
    }

    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    
    // Calculamos a qué unidad pertenece (0 = Bytes, 1 = KB, 2 = MB, etc.)
    $base = log($bytes, 1024);
    $unitIndex = floor($base);

    // Si por alguna razón el índice es mayor al arreglo, lo limitamos al máximo
    $unitIndex = min($unitIndex, count($units) - 1);

    // Calculamos el valor final
    $size = round(pow(1024, $base - floor($base)), 2);

    // Formateamos para que use la coma para decimales (opcional, estilo español)
    $formattedSize = number_format($size, $unitIndex > 0 ? 2 : 0, ',', '.');

    return $formattedSize . ' ' . $units[$unitIndex];
}
    /**
 * Devuelve metadatos del contenido de aprendizaje sin exponer la URL.
 * Ahora usa la relación Format, formatea la duración y el tamaño dinámico.
 */
private function formatLearningMeta($learningContent): ?array
{
    if (!$learningContent) {
        return null;
    }

    // Tipo de contenido (ej: 'link', 'archive')
    $typeName = $learningContent->relationLoaded('typeLearningContent') 
        ? strtolower($learningContent->typeLearningContent->name ?? '') 
        : null;

    // Nombre del formato (ej: 'youtube', 'mp4', 'pdf')
    $formatName = $learningContent->relationLoaded('format') 
        ? strtolower($learningContent->format->name ?? '') 
        : null;

    // Formatear duración estilo YouTube
    $formattedDuration = null;
    if ($learningContent->duration_seconds !== null) {
        $formattedDuration = $this->formatDuration($learningContent->duration_seconds);
    }

    // NUEVO: Tamaño dinámico (MB, KB, Bytes)
    $formattedSize = $this->formatFileSize($learningContent->size_bytes);

    return [
        'type'               => $typeName,
        'format'             => $formatName,
        'size'               => $formattedSize, // <-- Cambiado de size_mb a size
        'duration_seconds'   => $learningContent->duration_seconds,
        'duration_formatted' => $formattedDuration,
    ];
}

    /**
     * Convierte segundos a formato YouTube (ej: 1:05:30 o 45:12 o 0:35)
     */
    private function formatDuration(int $totalSeconds): string
    {
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        // Formatear segundos siempre a 2 dígitos (ej: 05 en lugar de 5)
        $secondsStr = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        if ($hours > 0) {
            // Si hay horas, los minutos deben tener 2 dígitos (ej: 1:05:30)
            $minutesStr = str_pad($minutes, 2, '0', STR_PAD_LEFT);
            return "{$hours}:{$minutesStr}:{$secondsStr}";
        }

        // Si no hay horas, formato m:ss (ej: 45:12, 0:35)
        return "{$minutes}:{$secondsStr}";
    }
    
    public function showDetail(Course $course)
    {
        // Autorización y estado del curso
        $this->authorize('view', $course);
        

        // Cargar relaciones necesarias
        $course->loadMissing([
            'difficulty:id,name',
            'careers:id,name,url_logo',
            'categories:id,name',
            'collaborators:id,name,lastname,username,profile_picture_url',
        ]);

        // Métricas de rating
        $ratingsCount = RatingCourse::where('course_id', $course->id)->count();
        $totalStars   = (int) RatingCourse::where('course_id', $course->id)->sum('stars');
        $avgStars     = $ratingsCount > 0 ? round($totalStars / $ratingsCount, 2) : null;

        // Si quieres incluir la calificación del usuario autenticado:
        $userId    = auth()->id();
        $userStars = $userId
            ? RatingCourse::where('course_id', $course->id)
                ->where('user_id', $userId)
                ->value('stars')
            : null;

        return response()->json([
            'ok'     => true,
            'course' => [
                'id'          => $course->id,
                'description' => $course->description,
                'created_at'  => $course->created_at,
                'difficulty'  => $course->difficulty
                    ? ['id' => $course->difficulty->id, 'name' => $course->difficulty->name]
                    : null,
                'careers'     => $course->careers->map(fn ($c) => [
                    'id'       => $c->id,
                    'name'     => $c->name,
                    'url_logo' => $c->url_logo,
                ])->values(),
                'categories'  => $course->categories->map(fn ($c) => [
                    'id'   => $c->id,
                    'name' => $c->name,
                ])->values(),
                'ratings'     => [
                    'count'       => $ratingsCount,
                    'total_stars' => $totalStars,
                    'avg_stars'   => $avgStars,
                    'user_stars'  => $userStars ? (int) $userStars : null,
                ],
                'collaborators' => $course->collaborators->map(fn ($u) => [
                    'name'  => $u->name,
                    'lastname'  => $u->lastname,
                    'username' => $u->username,
                    'profile_picture_url' => $u->profile_picture_url ? $u->profile_picture_url : null,
                ])->values(),
            ],
        ], 200);
    }
    public function showContent(Chapter $chapter)
    {
        // Cargamos módulo (para course_id), contenido, el tipo de contenido y AHORA el formato
        $chapter->loadMissing([
            'module:id,course_id',
            'learningContent' => function ($lq) {
                $lq->with([
                    'typeLearningContent:id,name',
                    'format:id,name' // <-- Agregamos la carga del formato aquí
                ]);
            },
        ])->loadCount([
            'questions',       // para has_questions
            'likeChapters',    // para likes_total
        ]);

        $course = Course::query()->findOrFail($chapter->module->course_id);

        $userId = auth()->id();
        $this->authorize('viewChapter', $chapter);

        // ¿El usuario guardó el curso?
        $isSaved = $userId
            ? SavedCourse::where('course_id', $course->id)->where('user_id', $userId)->exists()
            : false;

        // ¿El usuario dio like al capítulo?
        $userLiked = $userId
            ? LikeChapter::where('chapter_id', $chapter->id)->where('user_id', $userId)->exists()
            : false;

        // Totales y flags
        $likesTotal   = (int) ($chapter->like_chapters_count ?? 0);
        $hasQuestions = (int) ($chapter->questions_count ?? 0) > 0;

        // Último ContentView del usuario (para reanudar video)
        $lastView = null;
        if ($userId && $chapter->learningContent) {
            $lastViewModel = ContentView::where('learning_content_id', $chapter->learningContent->id)
                ->where('user_id', $userId)
                ->latest('updated_at')   // cámbialo a 'id' si no usas timestamps
                ->first();

            if ($lastViewModel) {
                $lastView = [
                    'second_seen' => $lastViewModel->second_seen,
                    'updated_at'  => optional($lastViewModel->updated_at)->toISOString(),
                ];
            }
        }

        // Dueño del curso
        $owner = $course->owner()
            ->select('users.id', 'users.name', 'users.lastname', 'users.profile_picture_url', 'users.username')
            ->first();

        // Meta del learning content (Usando tu función que ya formatea el tamaño)
    $learningMeta = $this->formatLearningMeta($chapter->learningContent);

    // Preparamos el array de learning content base
    $learningContentData = null;
    if ($chapter->learningContent) {
        $learningContentData = [
            'id' => $chapter->learningContent->id,
            'name' => $chapter->learningContent->name, // <-- Agregamos el name aquí
            'url' => $chapter->learningContent->url,
            'type_content_id' => $chapter->learningContent->type_content_id,
            'chapter_id' => $chapter->learningContent->chapter_id,
            'format_id' => $chapter->learningContent->format_id,
            // Usamos la misma lógica que en formatLearningMeta para la respuesta cruda
            'size' => $this->formatFileSize($chapter->learningContent->size_bytes), 
            'duration_seconds' => $chapter->learningContent->duration_seconds,
            'created_at' => $chapter->learningContent->created_at,
            'updated_at' => $chapter->learningContent->updated_at,
        ];
    }

        return response()->json([
            'ok'          => true,
            'user_state'  => [
                'is_saved'      => (bool) $isSaved,
                'liked_chapter' => (bool) $userLiked,
                'has_questions' => (bool) $hasQuestions,
            ],
            'chapter'     => [
                'id'          => $chapter->id,
                'title'       => $chapter->title,
                'description' => $chapter->description,
                'order'       => (int) $chapter->order,
                'module_id'   => (int) $chapter->module_id,
            ],
            'course'      => [
                'title'   => $course->title, // SOLO título
                'private' => $course->private,
            ],
            'owner'       => $owner ? [
                'name'                 => $owner->name,
                'lastname'             => $owner->lastname,
                'username'             => $owner->username,
                'is_owner'             => true,
                'profile_picture_url'  => $owner->profile_picture_url ?: null,
            ] : null,
            
            // El contenido crudo (con el size_mb casteado a float)
            'learning_content' => $learningContentData,
            
            // La meta con el formato limpio y la duración estilo YouTube
            'learning_meta'    => $learningMeta,  
            
            'last_view'     => $lastView,
            'likes_total'   => $likesTotal,
        ], 200);
    }

    private function getYouTubeDurationsInBulk(array $videoIds): array
    {
        if (empty($videoIds)) {
            return [];
        }

        $uniqueVideoIds = array_unique($videoIds);
        $cacheKey = 'youtube_durations_' . md5(implode(',', $uniqueVideoIds));

        // Usa Cache::remember para obtener datos de la caché o ejecutarlos si no existen.
        // Cache por 1 día (86400 segundos).
        return Cache::remember($cacheKey, 86400, function () use ($uniqueVideoIds) {
            try {
                $client = new Google_Client();
                $client->setDeveloperKey(env('YOUTUBE_API_KEY'));
                $youtube = new Google_Service_YouTube($client);

                // La API permite solicitar hasta 50 IDs a la vez, separados por comas.
                $videoResponse = $youtube->videos->listVideos('contentDetails', [
                    'id' => implode(',', $uniqueVideoIds),
                ]);

                $durations = [];
                foreach ($videoResponse->getItems() as $video) {
                    $isoDuration = $video->getContentDetails()->getDuration();
                    $durationInSeconds = $this->convertIso8601ToSeconds($isoDuration);

                    $durations[$video->getId()] = [
                        'duration_seconds' => $durationInSeconds,
                        'duration_formatted' => $this->formatDuration($durationInSeconds),
                    ];
                }
                return $durations;

            } catch (Exception $e) {
                // En caso de error, retorna un array vacío o maneja el error como prefieras.
                // Log::error('YouTube API error: ' . $e->getMessage());
                return [];
            }
        });
    }
    private function getApiYt($url){
        
        $videoId = $this->extractYtVideoId($url);

        if (!$videoId) {
            return response()->json(['error' => 'No se pudo extraer el ID del video de la URL proporcionada.'], 400);
        }

        try {
            
            
            $client = new Google_Client();
            $client->setDeveloperKey(env('YOUTUBE_API_KEY'));

            $youtube = new Google_Service_YouTube($client);

            $videoResponse = $youtube->videos->listVideos('contentDetails,snippet', [
                'id' => $videoId,
            ]);

            if (empty($videoResponse->getItems())) {
                return response()->json(['error' => 'Video no encontrado.'], 404);
            }

            $video = $videoResponse->getItems()[0];
            $isoDuration = $video->getContentDetails()->getDuration();
            $durationInSeconds = $this->convertIso8601ToSeconds($isoDuration);
            $durationFormatted = $this->formatDuration($durationInSeconds);

            return response()->json([
                'success' => true,
                'duration_seconds' => $durationInSeconds,
                'duration_formatted' => $durationFormatted
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Ocurrió un error con la API de YouTube: ' . $e->getMessage()], 500);
        }
    }
//     private function formatDuration($seconds)
// {
//     $minutes = floor($seconds / 60);
//     $seconds = $seconds % 60;

//     if ($minutes > 0) {
//         return $minutes . ' min ' . $seconds . ' s';
//     } else {
//         return $seconds . ' s';
//     }
// }
    public function showYt(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');
       return $this->getApiYt($url);
        
    }

    private function extractYtVideoId(string $url): ?string
    {
        $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Convierte una duración en formato ISO 8601 (ej. PT1M35S) a segundos.
     *
     * @param string $iso8601Duration
     * @return int
     */
    private function convertIso8601ToSeconds(string $iso8601Duration): int
    {
        $interval = new DateInterval($iso8601Duration);

        return ($interval->d * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }



}
