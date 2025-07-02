<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use App\Models\ContentView;
use App\Models\Module;
use App\Models\LearningContent;
use App\Models\Chapter;
use App\Models\ModuleAttempt;
// use App\Models\Module;
use Google_Client;
use Google_Service_YouTube;
use Exception;
use DateInterval;
class WatchingController extends Controller
{
    /**
     * Obtiene los detalles de un video de YouTube, incluyendo la duración en segundos.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
    public function getYtVideoDetail(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');
       return $this->getApiYt($url);
        
    }

    /**
     * Extrae el ID del video de varias formatos de URL de YouTube.
     *
     * @param string $url
     * @return string|null
     */
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

    public function getListContent($courseId,$userId)
{
    $course = Course::with([
        'categories' => function($q) {
            $q->where('enabled', true);
        },
        'registrations' => function($q) use ($userId) {
            $q->where('user_id', $userId);
        },
        'tutors' => function($q) {
            $q->wherePivot('enabled', true);
        },
        'modules' => function($q) {
            $q->where('enabled', true)->orderBy('order', 'asc');
        },
        'modules.chapters' => function($q) {
            $q->where('enabled', true)->orderBy('order', 'asc');
        },
        'modules.chapters.learningContent' => function($q) {
            $q->where('enabled', true);
        },
        'modules.chapters.learningContent.contentViews' => function($q) use ($userId) {
            $q->where('user_id', $userId);
        },
        'modules.chapters.learningContent.typeLearningContent' => function($q) {
            $q->where('enabled', true);
        },
    ])
    ->where('enabled', true)
    ->find($courseId);

    if (!$course) {
        return response()->json(['message' => 'Curso no encontrado'], 404);
    }
    
    $course->registered = $course->registrations->where('annulment', false)->isNotEmpty();
    
    // Obtener la duración de los videos de YouTube
    $course->modules->each(function($module) { 
        $module->chapters->each(function($chapter) {
            $learningContent = $chapter->learningContent;
            if ($learningContent->typeLearningContent->name == 'youtube-watch' || $learningContent->typeLearningContent->name == 'youtube-shorts') {
                $duration = $this->getApiYt($learningContent->url);
                $learningContent->duration_seconds = $duration->getData()->duration_seconds;
                $learningContent->duration_formatted = $duration->getData()->duration_formatted;
                
            }
            $learningContent->makeHidden(['url']);
           
        });
    });
    $course->last_view_id = $course->registered
    ? $course->modules->flatMap(function ($module) {
        return $module->chapters->flatMap(function ($chapter) {
            return $chapter->learningContent->contentViews;
        });
    })->sortByDesc('updated_at')->first()->id ?? null
    : $course->modules->first()->chapters->first()->learningContent->id;
    
    
    $course->tutors->each(function($tutor) {
        $tutor->makeHidden(['registration_method', 'email_verified_at', 'firebase_Uuid']);
    });

    return response()->json([
        'course' => $course
    ]);
}
/**
     * FUNCIÓN OPTIMIZADA
     */
    /* public function getListContent($courseId, $userId)
    {
        // 1. OPTIMIZACIÓN DE CONSULTA: Selecciona solo las columnas necesarias.
        // Asegúrate de incluir las claves foráneas (user_id, course_id, module_id, etc.)
        $course = Course::with([
            'categories:id,name',
            'registrations' => fn($q) => $q->where('user_id', $userId)->select('id', 'course_id', 'user_id', 'annulment'),
            'tutors:id,name,lastname,email',
            'modules' => fn($q) => $q->where('enabled', true)->orderBy('order')->select('id', 'name', 'order', 'course_id'),
            'modules.chapters' => fn($q) => $q->where('enabled', true)->orderBy('order')->select('id', 'name', 'order', 'module_id'),
            'modules.chapters.learningContent' => fn($q) => $q->where('enabled', true)->select('id', 'url', 'chapter_id', 'type_content_id'),
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
    }    */ 

public function getContentViewById($contentViewId, $registerId)
{
    // Obtener el contenido de aprendizaje asociado al ContentView
    $contentView = ContentView::with([
        'learningContent' => function ($q) {
            $q->where('enabled', true);
        },
        'learningContent.typeLearningContent' => function ($q) {
            $q->where('enabled', true);
        },
        'learningContent.chapter' => function ($q) {
            $q->where('enabled', true);
        },
    ])
    ->where('id', $contentViewId)
    ->where('user_id', $registerId)
    ->first();

    if (!$contentView) {
        return response()->json(['message' => 'ContentView no encontrado'], 404);
    }

    return response()->json([
        'content_view' => $contentView->only(['id', 'user_id', 'learning_content_id', 'second_seen']),
        'learning_content' => $contentView->learningContent->only(['id', 'url', 'enabled', 'type_content_id']),
        'type_learning_content' => $contentView->learningContent->typeLearningContent->only(['id', 'name', 'max_size', 'min_duration_seconds', 'max_duration_seconds']),
        'chapter' => $contentView->learningContent->chapter->only(['id', 'name', 'description', 'order', 'enabled']),
    ]);
}


}
