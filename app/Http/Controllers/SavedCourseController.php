<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\SavedCourse;
use App\Models\User;
use App\Models\Registration;
use App\Models\Chapter;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SavedCourseController extends Controller
{
    use AuthorizesRequests;
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'saved' => ['required', 'boolean'],
        ]);

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'No autenticado.'], 401);
        }

        // Solo cursos activos se pueden guardar
        if (!$course->enabled) {
            return response()->json([
                'ok' => false,
                'message' => 'El curso no está activo.'
            ], 403);
        }

        // Si quieres además restringir a quienes "pueden ver" el curso (opcional):
        // $this->authorize('view', $course);

        if ($data['saved'] === true) {
            // Crear si no existe
            SavedCourse::firstOrCreate([
                'user_id'   => $userId,
                'course_id' => $course->id,
            ]);
            $saved = true;
        } else {
            // Eliminar si existe
            SavedCourse::where('user_id', $userId)
                ->where('course_id', $course->id)
                ->delete();
            $saved = false;
        }

        return response()->json([
            'ok'    => true,
            'saved' => $saved,
        ], 200);
    }
}
