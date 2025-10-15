<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\LearningContent;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Registration;
use App\Models\User;
use App\Models\Course;
use App\Models\TypeLearningContent;
use App\Models\Module;
use App\Models\MiniatureCourse;
use App\Models\TutorCourse;
use App\Models\ContentView;
class ContentViewController extends Controller
{
    use AuthorizesRequests;
    public function update(Request $request,LearningContent $learningContent)
    {
        $data = $request->validate([
            'second_seen' => ['required', 'integer', 'min:0'],
        ]);

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'No autenticado.'], 401);
        }

        // Cargar lo necesario para validar reglas
        $learningContent->loadMissing([
            'typeLearningContent:id,name',
            'chapter:id,module_id,order',
            'chapter.module:id,course_id',
            'chapter.module.course:id,enabled',
        ]);

        $course  = $learningContent->chapter->module->course;
        $chapter = $learningContent->chapter;

        // Política y curso activo
        $this->authorize('view', $course);
        if (!$course->enabled) {
            return response()->json(['ok' => false, 'message' => 'El curso no está activo.'], 403);
        }

        // Registro requerido salvo capítulo de introducción (order = 1)
        $isIntro = ((int) ($chapter->order ?? 0)) === 1;
        if (!$isIntro) {
            $isRegistered = Registration::where('course_id', $course->id)
                ->where('user_id', $userId)
                ->exists();

            if (!$isRegistered) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Debes estar registrado para guardar tu progreso en este capítulo.',
                ], 403);
            }
        }

        // Solo permitimos progreso para Youtube o archivo de video
        $typeName = strtolower($learningContent->typeLearningContent->name ?? '');
        $isYouTube = $typeName === 'youtube';
        $isArchivo = in_array($typeName, ['archivo', 'archive', 'file'], true);

        if (!$isYouTube && !$isArchivo) {
            return response()->json([
                'ok' => false,
                'message' => 'Este contenido no admite registro de progreso.',
            ], 422);
        }

        // Si es archivo, verificar que sea realmente video por extensión
        if ($isArchivo) {
            $ext = $this->detectArchiveFormat($learningContent->url);
            $videoExts = ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv'];
            if (!$ext || !in_array($ext, $videoExts, true)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El contenido de archivo no es un video soportado.',
                ], 422);
            }
        }

        // Upsert de progreso (idempotente).
        // Sugerencia: guardamos el máximo para no “retroceder” progreso.
        $incoming = (int) $data['second_seen'];

        $view = ContentView::firstOrNew([
            'user_id'              => $userId,
            'learning_content_id'  => $learningContent->id,
        ]);

        if ($view->exists) {
            $view->second_seen = max((int) $view->second_seen, $incoming);
        } else {
            $view->second_seen = $incoming;
        }

        $view->save();

        return response()->json([
            'ok'          => true,
            'second_seen' => (int) $view->second_seen,
            'updated_at'  => optional($view->updated_at)->toISOString(),
        ], 200);
    }

    /**
     * Detecta extensión del recurso (mp4, pdf, etc.) a partir de la URL.
     * No expone la URL, solo la usa para inferir formato.
     */
    private function detectArchiveFormat(?string $url): ?string
    {
        if (!$url) return null;
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) return null;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $ext ?: null;
        // Nota: si usas una CDN sin extensión visible, considera persistir el formato al subir.
    }
}
