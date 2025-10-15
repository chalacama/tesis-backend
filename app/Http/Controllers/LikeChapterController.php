<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\LikeChapter;
use App\Models\Course;
use App\Models\Registration;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class LikeChapterController extends Controller
{
     use AuthorizesRequests;
    public function update(Request $request, Chapter $chapter)
    {
        $data = $request->validate([
            'liked' => ['required', 'boolean'],
        ]);

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'No autenticado.'], 401);
        }

        // Necesitamos el curso para aplicar las mismas reglas de acceso
        $chapter->loadMissing('module:id,course_id');
        $course = Course::findOrFail($chapter->module->course_id);

        $this->authorize('view', $course);

        if (!$course->enabled) {
            return response()->json(['ok' => false, 'message' => 'El curso no está activo.'], 403);
        }

        // Solo registrados pueden dar like, excepto si es el capítulo 1 (introducción)
        $isRegistered = Registration::where('course_id', $course->id)
            ->where('user_id', $userId)
            ->exists();

        if ((int)($chapter->order ?? 0) !== 1 && !$isRegistered) {
            return response()->json([
                'ok' => false,
                'message' => 'Debes estar registrado para dar like a este capítulo.'
            ], 403);
        }

        if ($data['liked'] === true) {
            // Crea si no existe
            LikeChapter::firstOrCreate([
                'user_id'    => $userId,
                'chapter_id' => $chapter->id,
            ]);
            $liked = true;
        } else {
            // Elimina si existe
            LikeChapter::where('user_id', $userId)
                ->where('chapter_id', $chapter->id)
                ->delete();
            $liked = false;
        }

        

        return response()->json([
            'ok'          => true,
            'liked'       => $liked,
            
        ], 200);
    }
}
