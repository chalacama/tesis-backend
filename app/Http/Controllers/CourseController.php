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
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
class CourseController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAnyHidden', Course::class);

        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $search = $request->query('search');
        $filters = $request->query('filters', []);

        $user = Auth::user();
        $cacheKey = 'courses_index_' . $user->id . '_page_' . $page . '_per_' . $perPage . '_search_' . md5($search ?? '') . '_filters_' . md5(json_encode($filters));

        $courses = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $perPage, $search, $filters) {
            $query = Course::query()->withTrashed();

            if ($user->hasRole('tutor')) {
                $query->whereHas('tutors', fn($q) => $q->where('users.id', $user->id));
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if (!empty($filters)) {
                if (isset($filters['enabled'])) {
                    $query->where('enabled', $filters['enabled']);
                }
                if (isset($filters['private'])) {
                    $query->where('private', $filters['private']);
                }
                if (isset($filters['difficulty_id'])) {
                    $query->where('difficulty_id', $filters['difficulty_id']);
                }
            }

            return $query
                ->with([
                    'miniature' => fn($q) => $q->select('miniature_courses.id', 'miniature_courses.course_id', 'miniature_courses.url'),
                    'categories' => fn($q) => $q->select('categories.id', 'categories.name'),
                    'certified' => fn($q) => $q->select('course_certifieds.id', 'course_certifieds.course_id', 'course_certifieds.is_certified'),
                    'tutors' => fn($q) => $q->select('users.id', 'users.name', 'users.lastname'),
                    'difficulty' => fn($q) => $q->select('difficulties.id', 'difficulties.name'),
                ])
                ->withCount(['modules', 'allComments', 'savedCourses', 'registrations'])
                ->withSum('ratingCourses as total_stars', 'stars')
                ->orderBy('courses.title')
                ->paginate($perPage)
                ->through(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'description' => $course->description,
                        'private' => $course->private,
                        'code' => $course->code,
                        'enabled' => $course->enabled,
                        'deleted_at' => $course->deleted_at,
                        'modules_count' => $course->modules_count,
                        'total_comments_count' => $course->all_comments_count,
                        'saved_courses_count' => $course->saved_courses_count,
                        'registrations_count' => $course->registrations_count,
                        'total_stars' => (int) $course->total_stars,
                        'is_certified' => $course->certified ? $course->certified->is_certified : false,
                        'miniature' => $course->miniature ? [
                            'id' => $course->miniature->id,
                            'url' => $course->miniature->url,
                        ] : null,
                        'difficulty' => $course->difficulty ? [
                            'id' => $course->difficulty->id,
                            'name' => $course->difficulty->name,
                        ] : null,
                        'categorias' => $course->categories->map(fn($cat) => [
                            'id' => $cat->id,
                            'name' => $cat->name,
                        ]),
                        'creador' => $this->getCreator($course),
                        'colaboradores' => $this->getCollaborators($course),
                    ];
                });
        });

        return response()->json([
            'courses' => $courses->items(),
            'pagination' => [
                'total' => $courses->total(),
                'per_page' => $courses->perPage(),
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
            ],
        ]);
    }

    private function getCreator(Course $course): string
    {
        $owner = $course->tutors->firstWhere('pivot.is_owner', true);
        return $owner ? ($owner->name . ' ' . ($owner->lastname ?? '')) : 'Digi Mentor';
    }

    private function getCollaborators(Course $course): array
    {
        return $course->tutors
            ->where('pivot.is_owner', false)
            ->sortBy(fn($tutor) => $tutor->lastname ?? '')
            ->map(fn($tutor) => $tutor->name . ' ' . ($tutor->lastname ?? ''))
            ->values()
            ->toArray();
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'private' => 'sometimes|boolean',
            'difficulty_id' => 'required|exists:difficulties,id',
        ]);

        $data = array_merge($validated, [
            'enabled' => false,
            'private' => $validated['private'] ?? false,
        ]);

        $course = Course::create($data);

        if (Auth::user()->hasRole('tutor')) {
            $course->tutors()->attach(Auth::id(), ['is_owner' => true]);
        }

        Cache::forget('courses_index_' . Auth::id() . '_page_*');

        return response()->json([
            'message' => 'Curso creado exitosamente',
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'private' => $course->private,
                'code' => $course->code,
                'enabled' => $course->enabled,
                'difficulty_id' => $course->difficulty_id,
            ],
        ], 201);
    }

    public function show(Course $course): JsonResponse
    {
        $this->authorize('viewHidden', $course);

        $user = Auth::user();
        $cacheKey = 'course_show_' . $course->id . '_user_' . $user->id;

        $courseInfo = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($course) {
            return Course::query()
                ->with([
                    'miniatures' => fn($q) => $q->select('miniature_courses.id', 'miniature_courses.course_id', 'miniature_courses.url'),
                    'categories' => fn($q) => $q->select('categories.id', 'categories.name'),
                    'certified' => fn($q) => $q->select('course_certifieds.id', 'course_certifieds.course_id', 'course_certifieds.is_certified'),
                    'tutors' => fn($q) => $q->select('users.id', 'users.name', 'users.lastname'),
                    'difficulty' => fn($q) => $q->select('difficulties.id', 'difficulties.name'),
                    'modules' => fn($q) => $q->withTrashed()
                        ->select('modules.id', 'modules.course_id', 'modules.name', 'modules.order', 'modules.deleted_at')
                        ->with([
                            'chapters' => fn($cq) => $cq->withTrashed()
                                ->select('chapters.id', 'chapters.module_id', 'chapters.title', 'chapters.description', 'chapters.order', 'chapters.deleted_at')
                                ->with([
                                    'learningContent' => fn($lcq) => $lcq->withTrashed()
                                        ->select('learning_contents.id', 'learning_contents.chapter_id', 'learning_contents.url', 'learning_contents.type_content_id')
                                        ->with([
                                            'typeLearningContent' => fn($tcq) => $tcq->select('type_learning_contents.id', 'type_learning_contents.name'),
                                        ]),
                                    'questions' => fn($qq) => $qq->withTrashed()
                                        ->select('questions.id', 'questions.chapter_id', 'questions.statement', 'questions.spot', 'questions.type_questions_id', 'questions.deleted_at')
                                        ->with([
                                            'answers' => fn($aq) => $aq->select('answers.id', 'answers.question_id', 'answers.option', 'answers.is_correct'),
                                            'typeQuestion' => fn($tqq) => $tqq->select('type_questions.id', 'type_questions.nombre'),
                                        ]),
                                ]),
                        ]),
                ])
                ->withCount(['modules', 'allComments', 'savedCourses', 'registrations'])
                ->withSum('ratingCourses as total_stars', 'stars')
                ->findOrFail($course->id);
        });

        $response = [
            'course' => [
                'id' => $courseInfo->id,
                'title' => $courseInfo->title,
                'description' => $courseInfo->description,
                'private' => $courseInfo->private,
                'code' => $courseInfo->code,
                'enabled' => $courseInfo->enabled,
                'difficulty' => $courseInfo->difficulty ? [
                    'id' => $courseInfo->difficulty->id,
                    'name' => $courseInfo->difficulty->name,
                ] : null,
                'deleted_at' => $courseInfo->deleted_at,
                'modules_count' => $courseInfo->modules_count,
                'total_comments_count' => $courseInfo->all_comments_count,
                'saved_courses_count' => $courseInfo->saved_courses_count,
                'registrations_count' => $courseInfo->registrations_count,
                'total_stars' => (int) $courseInfo->total_stars,
                'is_certified' => $courseInfo->certified ? $courseInfo->certified->is_certified : false,
                'categorias' => $courseInfo->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                ]),
                'creador' => $this->getCreator($courseInfo),
                'colaboradores' => $this->getCollaborators($courseInfo),
                'modulos' => $courseInfo->modules->map(fn($module) => [
                    'id' => $module->id,
                    'name' => $module->name,
                    'order' => $module->order,
                    'deleted_at' => $module->deleted_at,
                    'capitulos' => $module->chapters->map(fn($chapter) => [
                        'id' => $chapter->id,
                        'title' => $chapter->title,
                        'description' => $chapter->description,
                        'order' => $chapter->order,
                        'deleted_at' => $chapter->deleted_at,
                        'contenido' => $chapter->learningContent ? [
                            'id' => $chapter->learningContent->id,
                            'url' => $chapter->learningContent->url,
                            'type' => $chapter->learningContent->typeLearningContent ? $chapter->learningContent->typeLearningContent->name : null,
                        ] : null,
                        'preguntas' => $chapter->questions->map(fn($question) => [
                            'id' => $question->id,
                            'statement' => $question->statement,
                            'spot' => $question->spot,
                            'type' => $question->typeQuestion ? $question->typeQuestion->nombre : null,
                            'deleted_at' => $question->deleted_at,
                            'respuestas' => $question->answers->map(fn($answer) => [
                                'id' => $answer->id,
                                'option' => $answer->option,
                                'is_correct' => $answer->is_correct,
                            ]),
                        ]),
                    ]),
                ]),
            ],
        ];

        return response()->json($response);
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        if ($course->enabled) {
            return response()->json(['message' => 'El curso debe estar inactivo para editarlo'], 422);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'private' => 'sometimes|required|boolean',
            'difficulty_id' => 'sometimes|required|exists:difficulties,id',
        ]);

        if (isset($validated['private'])) {
            if ($validated['private'] && !$course->code) {
                $validated['code'] = Course::generateUniqueCode();
            } elseif (!$validated['private']) {
                $validated['code'] = null;
            }
        }

        $course->update($validated);

        Cache::forget('course_show_' . $course->id . '_user_' . Auth::id());
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget('courses_index_' . Auth::id() . '_page_' . $i . '_per_10');
            Cache::forget('courses_index_' . Auth::id() . '_page_' . $i . '_per_' . $request->query('per_page', 10));
        }

        return response()->json(['message' => 'Curso actualizado', 'course' => $course]);
    }

    public function resetCode(Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        if (!$course->private) {
            return response()->json(['message' => 'El curso debe ser privado para restablecer el código'], 422);
        }

        $newCode = Course::generateUniqueCode();

        $course->update(['code' => $newCode]);

        Cache::forget('course_show_' . $course->id . '_user_' . Auth::id());
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget('courses_index_' . Auth::id() . '_page_' . $i . '_per_10');
            Cache::forget('courses_index_' . Auth::id() . '_page_' . $i . '_per_10');
        }

        return response()->json([
            'message' => 'Código del curso restablecido',
            'course' => [
                'id' => $course->id,
                'code' => $course->code,
            ],
        ]);
    }

    public function active(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $course->update(['enabled' => $validated['enabled']]);

        Cache::forget('course_show_' . $course->id . '_user_' . Auth::id());
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget('courses_index_' . Auth::id() . '_page_' . $i . '_per_10');
            Cache::forget('courses_index_' . Auth::id() . '_page_' . $i . '_per_' . $request->query('per_page', 10));
        }

        return response()->json([
            'message' => 'Estado del curso actualizado',
            'course' => [
                'id' => $course->id,
                'enabled' => $course->enabled,
            ],
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return response()->json(['message' => 'Curso enviado a papelería']);
    }

    /* public function restore(string $id): JsonResponse
    {
        $this->authorize('restore', $course);
        $course = Course::onlyTrashed()->findOrFail($id);

        $course->restore();

        Cache::forget('course_show_' . $course->id . '_user_' . Auth::id());
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget('courses_index_' . Auth::id() . '_page_' . $i . '_per_10');
            Cache::forget('courses_index_' . Auth::id() . '_page_' . $i . '_per_10');
        }

        return response()->json(['message' => 'Curso restaurado exitosamente']);
    } */

    
}
