<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\TutorCourse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class CourseController extends Controller
{
    /* public function createCourse(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            // Los demás campos toman el valor por defecto de la migración
        ]);

        return response()->json([
            'message' => 'Curso creado correctamente',
            'course' => $course
        ], 201);
    } */
   public function createCourse(Request $request): JsonResponse
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    $user = Auth::user();

    if ($user->hasRole('student')) {
        return response()->json([
            'success' => false,
            'message' => 'Los estudiantes no pueden crear cursos.',
        ], 403);
    }

    try {
        DB::beginTransaction();

        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        $tutorCourseCreated = false;

        // Si es tutor, se asigna automáticamente al curso
        if ($user->hasRole('tutor')) {
            TutorCourse::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enabled' => true,
            ]);
            $tutorCourseCreated = true;
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => $tutorCourseCreated
                ? 'Curso creado y asignado al tutor correctamente.'
                : 'Curso creado correctamente por el administrador.',
            'course' => $course,
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Error al crear curso', ['error' => $e]);

        return response()->json([
            'success' => false,
            'message' => 'Error interno al crear el curso.',
            'error' => config('app.debug') ? $e->getMessage() : 'Ha ocurrido un error inesperado.'
        ], 500);
    }
}
    public function updateCourse(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]); 

        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $course->update($validated);

        // Solo retorna los campos necesarios
        return response()->json([
            'message' => 'Curso actualizado correctamente',
            'course' => $course
        ]);
    }
    public function activateCourse(Request $request, $id)
    {
        $validated = $request->validate([
            'activate' => 'required|boolean',
        ]);

        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
        if ($validated['activate'] && $course->enabled) {
        return response()->json([
            'message' => 'El Curso ya está activado',
            'course' => $course
        ]);
    }
    if (!$validated['activate'] && !$course->enabled) {
        return response()->json([
            'message' => 'El Curso ya está desactivado',
            'module' => $course
        ]);
    }

        if ($validated['activate']) {
            $course->enabled = true;
            
        } else {
            $course->enabled = false;
            
        }

        $course->save();

        return response()->json([
            'message' => $validated['activate'] ? 'Curso publicado correctamente' : 'Curso archivado correctamente',
            'course' => $course
        ]);
    }
    public function softDeleteCourse($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $course->delete();

        return response()->json([
            'message' => 'Curso enviado a papelería correctamente'
        ]);
    }
    
    public function getCourseDetail($id)
    { 
        $course = Course::with([
            'categories',            // categorías del curso
            'tutors',                // tutores del curso
            'modules.chapters.learningContent', 
            'modules.chapters.questions.typeQuestion', 
            'modules.chapters.questions.answers',      
        ])->find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        return response()->json([
            'course' => $course
        ]);
    }
/**
     * Obtiene todos los cursos con su información agregada para una API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /* public function getAllCourses(): JsonResponse
    {
        $courses = Course::query()
            // Cargar relaciones con condiciones
            ->with([
                'miniatures' => fn($query) => $query->where('enabled', true),
                'categories' => fn($query) => $query->where('enabled', true),
                'certified:id,course_id,is_certified', // Cargar solo campos necesarios
            ])
            // Contar módulos activos
            ->withCount(['modules as active_modules_count' => fn($query) => $query->where('enabled', true)])
            // Contar el total de comentarios y respuestas de una sola vez
            ->withCount(['allComments as total_comments_count' => fn($query) => $query->where('enabled', true)])
            // Contar guardados e inscripciones
            ->withCount(['savedCourses', 'registrations'])
            // Sumar todas las estrellas de calificación
            ->withSum('ratingCourses as total_stars', 'stars')
            // Obtener solo cursos que están habilitados
            ->where('enabled', true)
            ->get()
            // Transformar la colección para una respuesta de API limpia
            ->map(function ($course) {
                // El campo 'total_stars' puede ser null si no hay calificaciones, lo convertimos a 0.
                $course->total_stars = (int) $course->total_stars;

                // Simplificar la información del certificado
                $course->is_certified = $course->certified ? $course->certified->is_certified : false;

                // Formatear las categorías para que solo muestren id y nombre
                $course->categorias = $course->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name
                ]);

                // Eliminar las relaciones completas para no exponer datos innecesarios en la API
                unset($course->certified, $course->categories);

                return $course;
            });

        return response()->json(['courses' => $courses]);
    } */
public function getAllCourses(): JsonResponse
{
    $user = Auth::user();

    if ($user->hasRole('student')) {
        return response()->json([
            'message' => 'Acceso denegado. Solo administradores o tutores pueden ver los cursos.',
        ], 403);
    }

    $query = Course::query()
        ->with([
            'miniatures' => fn($q) => $q->where('enabled', true),
            'categories' => fn($q) => $q->where('enabled', true),
            'certified:id,course_id,is_certified',
        ])
        ->withCount([
            'modules as active_modules_count' => fn($q) => $q->where('enabled', true),
            'allComments as total_comments_count' => fn($q) => $q->where('enabled', true),
            'savedCourses',
            'registrations',
        ])
        ->withSum('ratingCourses as total_stars', 'stars')
        ->where('enabled', true);

    // Si es tutor, filtra por cursos asignados
    if ($user->hasRole('tutor')) {
        $query->whereHas('tutors', fn($q) => $q->where('users.id', $user->id));
    }

    $courses = $query->get()
        ->map(function ($course) {
            $course->total_stars = (int) $course->total_stars;
            $course->is_certified = $course->certified ? $course->certified->is_certified : false;

            $course->categorias = $course->categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name
            ]);

            unset($course->certified, $course->categories);

            return $course;
        });

    return response()->json(['courses' => $courses]);
}
}
