<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LearningContent;
use App\Models\ContentView;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContentViewController extends Controller
{
    use AuthorizesRequests;

    // Formatos de video y audio que admiten tracking de segundos
    private const VIDEO_FORMATS = ['video', 'google.video', 'onedrive.video', 'youtube'];
    private const AUDIO_FORMATS = ['audio', 'googledrive.audio', 'onedrive.audio'];

    public function update(Request $request, LearningContent $learningContent)
    {
        $data = $request->validate([
            'second_seen' => ['required', 'integer', 'min:0'],
        ]);

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'No autenticado.'], 401);
        }

        // Cargamos type, format y la cadena chapter → module → course
        $learningContent->loadMissing([
            'typeLearningContent:id,name',
            'format:id,name',               // <-- NUEVO: relación al formato
            'chapter:id,module_id,order',
            'chapter.module:id,course_id',
            'chapter.module.course:id,enabled',
        ]);

        $chapter = $learningContent->chapter;

        $this->authorize('viewChapter', $chapter);

        // ── Determinar si este contenido admite tracking de segundos ──────────
        $typeName   = strtolower($learningContent->typeLearningContent->name ?? '');
        $formatName = strtolower($learningContent->format->name ?? '');

        $allowed = match(true) {
            // link/youtube  →  video de YouTube
            $typeName === 'link'    &&  in_array($formatName, self::VIDEO_FORMATS)  => true,
            // archive/mp4…  →  video subido
            $typeName === 'archive' && in_array($formatName, self::VIDEO_FORMATS)   => true,
            // archive/mp3   →  audio subido (NUEVO)
            $typeName === 'archive' && in_array($formatName, self::AUDIO_FORMATS)   => true,
            default                                                                 => false,
        };

        if (!$allowed) {
            return response()->json([
                'ok'      => false,
                'message' => 'Este contenido no admite registro de progreso por segundos.',
            ], 422);
        }

        // ── Upsert: siempre guardamos el máximo (no retrocedemos progreso) ─────
        $incoming = $data['second_seen'];

        $view = ContentView::firstOrNew([
            'user_id'             => $userId,
            'learning_content_id' => $learningContent->id,
        ]);

        $view->second_seen = $view->exists
            ? max($view->second_seen, $incoming)
            : $incoming;

        $view->save();

        return response()->json([
            'ok'          => true,
            'second_seen' => $view->second_seen,
            'updated_at'  => optional($view->updated_at)->toISOString(),
        ]);
    }
}