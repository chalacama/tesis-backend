<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use Google_Client;
use Google_Service_YouTube;
use Exception;
use DateInterval;
class RegistrationController extends Controller
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

    public function getCourseDetail($courseId,$userId)
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
        // registrations
        'modules' => function($q) {
            $q->where('enabled', true);
        },
        'modules.chapters' => function($q) {
            $q->where('enabled', true);
        },
        'modules.chapters.learningContent' => function($q) {
            $q->where('enabled', true);
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
    
    $course->registered = $course->registrations->isNotEmpty();
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
    // Ocultar campos en la respuesta
    $course->tutors->each(function($tutor) {
        $tutor->makeHidden(['registration_method', 'email_verified_at', 'firebase_Uuid']);
    });

    return response()->json([
        'course' => $course
    ]);
}

public function registerUserToCourse(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer',
        'course_id' => 'required|integer',
    ]);

    $userId = $request->input('user_id');
    $courseId = $request->input('course_id');

    // Verificar si el usuario y el curso existen
    $user = User::find($userId);
    $course = Course::find($courseId);

    if (!$course || !$course->enabled) {
    return response()->json(['error' => 'El curso no está activo o no existe'], 400);
    }

    // Verificar si el usuario ya está registrado en el curso
    $registration = Registration::where('user_id', $userId)->where('course_id', $courseId)->first();

    if ($registration) {
        return response()->json(['error' => 'Usuario ya registrado en el curso'], 400);
    }

    // Registrar al usuario en el curso
    $registration = new Registration();
    $registration->user_id = $userId;
    $registration->course_id = $courseId;
    $registration->save();

    return response()->json(['message' => 'Usuario registrado en el curso con éxito']);
}
}
