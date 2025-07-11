<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\TutorCourse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class CourseController extends Controller
{
    use AuthorizesRequests;
   /* public function createCourse(Request $request): JsonResponse
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
} */
public function createCourse(Request $request): JsonResponse
    {
        // Laravel automáticamente usará CoursePolicy@create
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // La lógica de creación es la misma...
        $course = Course::create($validated);
        
        // Si es tutor, se asigna automáticamente al curso
        if (Auth::user()->hasRole('tutor')) {
            $course->tutors()->attach(Auth::id(), ['enabled' => true]);
        }

        return response()->json(['message' => 'Curso creado', 'course' => $course], 201);
    }

    public function updateCourse(Request $request, Course $course): JsonResponse // Usando Route Model Binding
    {
        // Laravel automáticamente pasará el $course a CoursePolicy@update
        // Si la autorización falla, lanzará una excepción 403 Forbidden automáticamente.
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        $course->update($validated);

        return response()->json(['message' => 'Curso actualizado', 'course' => $course]);
    }
    
    public function activateCourse(Request $request, Course $course): JsonResponse
    {
        $this->authorize('activate', $course);

        // ... tu lógica para activar/desactivar ...
        $validated = $request->validate(['activate' => 'required|boolean']);
        $course->enabled = $validated['activate'];
        $course->save();

        return response()->json(['message' => 'Estado del curso actualizado', 'course' => $course]);
    }
    
    public function softDeleteCourse(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return response()->json(['message' => 'Curso enviado a papelería']);
    }

    public function getAllCourses(): JsonResponse
{
    // Autoriza si el usuario puede ver la lista de cursos del backend
    $this->authorize('viewAny', Course::class);

    $user = Auth::user();
    $query = Course::query(); // La query base

    // Si es tutor, la policy ya nos dio acceso, ahora filtramos para que vea solo los suyos.
    if ($user->hasRole('tutor')) {
        $query->whereHas('tutors', fn($q) => $q->where('users.id', $user->id));
    }
    // Si es admin, no se aplica el filtro y ve todos los cursos.

    $courses = $query
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
        ->where('enabled', true)
        ->get()
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
public function getCourseDetail(Course $course):JsonResponse
    { 
        $courseInfo = Course::with([
            'categories',            // categorías del curso
            'tutors',                // tutores del curso
            'modules.chapters.learningContent', 
            'modules.chapters.questions.typeQuestion', 
            'modules.chapters.questions.answers',      
        ])->find($course);

        if (!$courseInfo) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        return response()->json([
            'course' => $course
        ]);
    }
}
