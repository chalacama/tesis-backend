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
use App\Models\ContentChapter;
use App\Models\LikeChapter;
use App\Models\CompletedChapter;

use App\Models\LearningContent;
use App\Models\ContentView;
use App\Models\TypeLearningContent;


class WatchingController extends Controller
{
     use AuthorizesRequests;
    public function showCourse(Course $course)
    {
        $this->authorize('view', $course);

        // 1) Mostrar SOLO si el curso está activo
        if (!$course->enabled) {
            return response()->json([
                'ok'      => false,
                'message' => 'El curso no está activo.',
            ], 403);
        }

        $userId = auth()->id();

        // 2) Eager load para navegación (módulos → capítulos) + métricas
        $course->load([
            'modules' => function ($q) use ($userId) {
                $q->orderBy('order')
                  ->select('id', 'name', 'order', 'course_id', 'updated_at', 'created_at')
                  ->with([
                      'chapters' => function ($cq) use ($userId) {
                          $cq->orderBy('order')
                             ->select('id', 'title', 'description', 'order', 'module_id', 'updated_at', 'created_at')
                             ->withCount('questions')
                             ->withMax('questions', 'updated_at') // alias: questions_max_updated_at
                             ->with([
                                 'learningContent' => function ($lq) {
                                     $lq->select('id', 'chapter_id', 'url', 'type_content_id', 'updated_at')
                                        ->with(['typeLearningContent:id,name']);
                                 },
                                 'completedChapters' => function ($ccq) use ($userId) {
                                     if ($userId) {
                                         $ccq->select('id', 'chapter_id', 'user_id', 'content_at', 'test_at')
                                             ->where('user_id', $userId)
                                             ->whereNotNull('content_at')
                                             ->whereNotNull('test_at');
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
                            return [
                                'id'              => $c->id,
                                'title'           => $c->title,
                                'description'     => $c->description,
                                'order'           => $c->order,
                                'updated_at'      => $c->updated_at,
                                'created_at'      => $c->created_at,
                                'questions_count' => $c->questions_count,

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
        // baseline: último cambio del contenido o de alguna pregunta
        $learningUpdated = null;
        if ($chapter->relationLoaded('learningContent') && $chapter->learningContent) {
            $learningUpdated = $chapter->learningContent->updated_at
                ? Carbon::parse($chapter->learningContent->updated_at)
                : null;
        }

        $questionsMaxUpdated = null;
        // Eloquent agrega el alias: questions_max_updated_at
        if (!empty($chapter->questions_max_updated_at)) {
            $questionsMaxUpdated = Carbon::parse($chapter->questions_max_updated_at);
        }

        /** @var Carbon|null $baseline */
        $baseline = collect([$learningUpdated, $questionsMaxUpdated])
            ->filter()
            ->max(); // Carbon o null

        // Buscar el registro de completado más "reciente" (cuando ya tiene ambos marcados)
        if (!$chapter->relationLoaded('completedChapters')) {
            return [
                'is_completed' => false,
                'content_at'   => null,
                'test_at'      => null,
                'completed_at' => null,
            ];
        }

        $candidate = $chapter->completedChapters
            ->sortByDesc(function ($r) {
                $c = $r->content_at ? Carbon::parse($r->content_at) : null;
                $t = $r->test_at ? Carbon::parse($r->test_at) : null;
                $max = collect([$c, $t])->filter()->max();
                return $max ? $max->timestamp : 0;
            })
            ->first();

        if (!$candidate) {
            return [
                'is_completed' => false,
                'content_at'   => null,
                'test_at'      => null,
                'completed_at' => null,
            ];
        }

        $contentAt = $candidate->content_at ? Carbon::parse($candidate->content_at) : null;
        $testAt    = $candidate->test_at ? Carbon::parse($candidate->test_at) : null;

        // Regla: si cualquiera es null → no completado
        if (!$contentAt || !$testAt) {
            return [
                'is_completed' => false,
                'content_at'   => $candidate->content_at,
                'test_at'      => $candidate->test_at,
                'completed_at' => null,
            ];
        }

        // Regla: ambas fechas deben ser > baseline (si baseline existe)
        $passesBaseline = true;
        if ($baseline instanceof Carbon) {
            $passesBaseline = $contentAt->gt($baseline) && $testAt->gt($baseline);
        }

        $isCompleted = $passesBaseline;

        return [
            'is_completed' => $isCompleted,
            'content_at'   => $candidate->content_at,
            'test_at'      => $candidate->test_at,
            'completed_at' => $isCompleted ? $contentAt->max($testAt) : null,
        ];
    }
    /**
     * Devuelve metadatos del contenido de aprendizaje sin exponer la URL.
     * - type: 'youtube' | 'archivo'
     * - format: extensión (solo si type == 'archivo', p.ej. 'mp4', 'pdf'); en otros casos null
     */
    private function formatLearningMeta($learningContent): ?array
    {
        if (!$learningContent || !$learningContent->relationLoaded('typeLearningContent')) {
            return null;
        }

        $typeName = strtolower($learningContent->typeLearningContent->name ?? '');

        // Solo inferimos formato para 'archivo' usando la URL (sin devolver la URL)
        $format = null;
        if ($typeName === 'archivo') {
            $format = $this->detectArchiveFormat($learningContent->url);
        }

        // Para youtube, devolvemos type 'youtube' y format null
        return [
            'type'   => in_array($typeName, ['youtube', 'archivo']) ? $typeName : $typeName,
            'format' => $format, // null para youtube; extensión para archivo
        ];
    }

    /**
     * Detecta la extensión del recurso (mp4, pdf, etc.) a partir de la URL.
     * No expone la URL, solo la usa para inferir formato.
     */
    private function detectArchiveFormat(?string $url): ?string
    {
        if (!$url) return null;

        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) return null;

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $ext ?: null;
    }
    
    public function showDetail(Course $course)
    {
        // Autorización y estado del curso
        $this->authorize('view', $course);
        if (!$course->enabled) {
            return response()->json([
                'ok' => false,
                'message' => 'El curso no está activo.',
            ], 403);
        }

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
        // Cargamos módulo (para course_id), contenido y tipo del contenido
        $chapter->loadMissing([
            'module:id,course_id',
            'learningContent.typeLearningContent:id,name',
        ])->loadCount([
            'questions',       // para has_questions
            'likeChapters',    // para likes_total
        ]);

        $course = Course::query()->findOrFail($chapter->module->course_id);

        // Autorización y estado del curso
        $this->authorize('view', $course);
        if (!$course->enabled) {
            return response()->json([
                'ok'      => false,
                'message' => 'El curso no está activo.',
            ], 403);
        }

        $userId = auth()->id();

        // ¿El usuario está registrado?
        $isRegistered = $userId
            ? Registration::where('course_id', $course->id)->where('user_id', $userId)->exists()
            : false;

        // Regla: solo registrados, excepto si es el primer capítulo (order = 1)
        if ((int)($chapter->order ?? 0) !== 1 && !$isRegistered) {
            return response()->json([
                'ok'      => false,
                'message' => 'Debes estar registrado en el curso para ver este capítulo.',
            ], 403);
        }

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
                    'second_seen' => (int) $lastViewModel->second_seen,
                    'updated_at'  => optional($lastViewModel->updated_at)->toISOString(),
                ];
            }
        }

        // Dueño del curso
        $owner = $course->owner()
            ->select('users.id', 'users.name', 'users.lastname', 'users.profile_picture_url', 'users.username')
            ->first();

        // Meta del learning content (tipo y formato si es archivo)
        $learningMeta = $this->formatLearningMeta($chapter->learningContent);

        return response()->json([
            'ok'          => true,
            'user_state'  => [
                'is_saved'      => (bool) $isSaved,
                'is_registered' => (bool) $isRegistered,
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
                'title' => $course->title, // SOLO título
            ],
            'owner'       => $owner ? [
                'name'                 => $owner->name,
                'lastname'             => $owner->lastname,
                'username'             => $owner->username,
                'is_owner'             => true,
                'profile_picture_url'  => $owner->profile_picture_url ?: null,
            ] : null,
            'learning_content' => $chapter->learningContent
                ? $chapter->learningContent->toArray()
                : null,
            'learning_meta' => $learningMeta,   // <-- tipo y formato (si aplica)
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
    public function indexCourse($courseId, $userId)
    {
        // 1. OPTIMIZACIÓN DE CONSULTA: Selecciona solo las columnas necesarias.
        // Asegúrate de incluir las claves foráneas (user_id, course_id, module_id, etc.)
        $course = Course::with([
            'categories:id,name',
            'registrations' => fn($q) => $q->where('user_id', $userId)->select('id', 'course_id', 'user_id', 'annulment'),
            'tutors:id,name,lastname,email',
            'modules' => fn($q) => $q->orderBy('order')->select('id', 'name', 'order', 'course_id'),
            'modules.chapters' => fn($q) => $q->orderBy('order')->select('id', 'title', 'order', 'module_id'),
            'modules.chapters.learningContent' => fn($q) => $q->select('id', 'url', 'chapter_id', 'type_content_id'),
            'modules.chapters.learningContent.contentViews' => fn($q) => $q->where('user_id', $userId)->select('id', 'learning_content_id', 'user_id', 'updated_at'),
            'modules.chapters.learningContent.typeLearningContent:id,name',
        ])
        ->where('enabled', true)
        ->select('id', 'title', 'description', 'enabled') // Selecciona solo campos necesarios del curso
        ->find($courseId);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        // 2. OBTENCIÓN MASIVA DE DURACIONES (SIN BUCLES DE API)
        $videoContents = $course->modules->flatMap(fn($module) => $module->chapters)
            ->pluck('learningContent')
            ->filter(function ($content) {
                return $content && in_array($content->typeLearningContent->name, ['youtube-watch', 'youtube-shorts']);
            });

        $videoIds = $videoContents->map(fn($content) => $this->extractYtVideoId($content->url))->filter()->all();
        
        // Hacemos una única llamada para todas las duraciones
        $durations = $this->getYouTubeDurationsInBulk($videoIds);

        // Asignamos las duraciones a cada contenido
        $videoContents->each(function ($content) use ($durations) {
            $videoId = $this->extractYtVideoId($content->url);
            if (isset($durations[$videoId])) {
                $content->duration_seconds = $durations[$videoId]['duration_seconds'];
                $content->duration_formatted = $durations[$videoId]['duration_formatted'];
            }
        });

        // 3. PASAR LA LÓGICA DE TRANSFORMACIÓN A UN API RESOURCE
        // La lógica de 'registered', 'last_view_id', y 'makeHidden' se moverá al Resource.
        return new CourseResource($course);
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
    private function formatDuration($seconds)
{
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;

    if ($minutes > 0) {
        return $minutes . ' min ' . $seconds . ' s';
    } else {
        return $seconds . ' s';
    }
}
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


/* public function showContent(Request $request)
{
    $contentViewId = $request->input('content_view_id');
    // Obtener el contenido de aprendizaje asociado al ContentView
    $contentView = ContentView::with([
        'learningContent',
        'learningContent.typeLearningContent' ,
        'learningContent.chapter'
       
    ])
    ->where('id', $contentViewId)
    ->first();

    if (!$contentView) {
        return response()->json(['message' => 'ContentView no encontrado'], 404);
    } 

    return response()->json([
        'content_view' => $contentView->only(['id', 'user_id', 'learning_content_id', 'second_seen']),
        'learning_content' => $contentView->learningContent->only(['id', 'url', 'type_content_id']),
        'type_learning_content' => $contentView->learningContent->typeLearningContent->only(['id', 'name', 'max_size', 'min_duration_seconds', 'max_duration_seconds']),
        'chapter' => $contentView->learningContent->chapter->only(['id', 'name', 'description', 'order']),
    ]);
} */


}
