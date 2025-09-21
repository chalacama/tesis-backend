<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Course;
use App\Models\MiniatureCourse;
use App\Models\User;
use App\Models\TutorCourse;
class MiniatureCourseController extends Controller
{
    use AuthorizesRequests;
    public function show(Course $course): JsonResponse
    {
        // 1) Policy: solo el dueño (o admin por el before) puede ver
        $this->authorize('viewHidden', $course);

        // 2) Traer la miniatura
        $miniature = $course->miniature()->first();


        // 3) Respuesta
        return response()->json([
            'course' => $course,
            'miniature' => $miniature ? $miniature : null
           
        ]);
    }
}
