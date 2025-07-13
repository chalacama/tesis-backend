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
    public function index(): JsonResponse
{
    $this->authorize('viewAny', Course::class);

    $user = Auth::user();
    $query = Course::query()->withTrashed();

    if ($user->hasRole('tutor')) {
        $query->whereHas('tutors', fn($q) => $q->where('users.id', $user->id));
    }

    $courses = $query
        ->with([
            'miniatures',
            'categories',
            'certified:id,course_id,is_certified',
            'tutors.userInformation', // Para obtener apellido
        ])
        ->withCount([
            'modules as modules_count',
            'allComments as total_comments_count',
            'savedCourses',
            'registrations',
        ])
        ->withSum('ratingCourses as total_stars', 'stars')
        ->orderBy('title') // Orden alfabético de cursos
        ->get()
        ->map(function ($course) {
            $course->total_stars = (int) $course->total_stars;
            $course->is_certified = $course->certified ? $course->certified->is_certified : false;
            $course->categorias = $course->categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name
            ]);
            unset($course->certified, $course->categories);

            // Tutores
            $tutores = $course->tutors->map(function ($tutor) {
                return [
                    'id' => $tutor->id,
                    'name' => $tutor->name,
                    'lastname' => $tutor->userInformation->lastname ?? '',
                    'is_owner' => (bool) $tutor->pivot->is_owner,
                ];
            });

            // Ordena colaboradores por apellido
            $colaboradores = $tutores->where('is_owner', false)->sortBy('lastname')->values();
            $dueno = $tutores->where('is_owner', true)->first();

            if (!$dueno && $tutores->isEmpty()) {
                $course->creador = 'ESPAM MFL';
                $course->colaboradores = [];
            } else {
                $course->creador = $dueno ? ($dueno['name'] . ' ' . $dueno['lastname']) : 'ESPAM MFL';
                $course->colaboradores = $colaboradores->map(fn($col) => $col['name'] . ' ' . $col['lastname']);
            }

            return $course;
        });

    return response()->json(['courses' => $courses]);
}  
    public function store(Request $request): JsonResponse
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
    public function show(Course $course):JsonResponse
    { 
        $this->authorize('view', $course);
        $courseInfo = Course::with([
            // Incluye módulos eliminados
            'modules' => function ($query) {
                $query->withTrashed()
                    ->with([
                        // Incluye capítulos eliminados
                        'chapters' => function ($chapterQuery) {
                            $chapterQuery->withTrashed()
                                ->with([
                                    // Incluye contenidos eliminados
                                    'learningContent' => function ($lcQuery) {
                                        $lcQuery->withTrashed();
                                    }
                                ]);
                        }
                    ]);
            }      
        ])->find($course->id);

        if (!$courseInfo) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        return response()->json([
            'course' => $courseInfo
        ]);
    }
    public function update(Request $request, Course $course): JsonResponse // Usando Route Model Binding
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
    public function destroy(Post $post)
    {
        /* $post->delete();
        return response()->json(null, 204); */
    }
    public function restore(string $id)
    {
        /* $item = YourModel::onlyTrashed()->find($id);

        if (!$item) {
        return response()->json(['message' => 'Item not found in archive.'], 404);
        }

        $item->restore();
        return response()->json(['message' => 'Item restored successfully.']); */
    }     
    public function archived(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return response()->json(['message' => 'Curso enviado a papelería']);
    }

    
}
