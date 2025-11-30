<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\RatingCourse;
use App\Models\Registration;

class RatingCourseController extends Controller
{
    /**
     * Calificar / actualizar la calificación de un curso.
     * Ruta: POST /feedback/rating/{course}/update
     */
    public function update(Request $request, Course $course)
    {
        // Usuario autenticado (Sanctum)
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'ok'      => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        // 1) Verificar que el usuario esté inscrito en el curso
        $registration = Registration::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$registration) {
            return response()->json([
                'ok'      => false,
                'message' => 'Debes estar inscrito en este curso para poder calificarlo.',
            ], 403);
        }

        // 2) Verificar que tenga al menos un certificado asociado a esa inscripción
        $hasCertificate = $registration->certificates()->exists();

        if (!$hasCertificate) {
            return response()->json([
                'ok'      => false,
                'message' => 'Debes haber obtenido un certificado de este curso para poder calificarlo.',
            ], 403);
        }

        // 3) Validar las estrellas (1 a 5)
        $validated = $request->validate([
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        // 4) Crear o actualizar la calificación del usuario para este curso
        $rating = RatingCourse::updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id'   => $user->id,
            ],
            [
                'stars'     => $validated['stars'],
            ]
        );

        // 5) Recalcular promedio y cantidad de calificaciones del curso
        $average = round($course->ratingCourses()->avg('stars') ?? 0, 2);
        $count   = $course->ratingCourses()->count();

        return response()->json([
            'ok'      => true,
            'message' => 'Calificación registrada correctamente.',
            'data'    => [
                'course_id'      => $course->id,
                'user_id'        => $user->id,
                'stars'          => $rating->stars,
                'average_stars'  => $average,
                'ratings_count'  => $count,
            ],
        ], 200);
    }
}


