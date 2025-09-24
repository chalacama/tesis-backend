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
use App\Models\User;
use Carbon\Carbon;
use App\Models\MiniatureCourse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
class CourseController extends Controller
{

    use AuthorizesRequests;
    public function index(Request $request): JsonResponse
    {
    $this->authorize('viewAnyHidden', Course::class);

    $perPage = $request->query('per_page', 10);
    $search = $request->query('search');
    $filters = $request->query('filters', []);
    $user = Auth::user();

    $query = Course::query()->withTrashed();

    // 🔐 Filtro para tutores: solo ver cursos donde colaboran
    if ($user->hasRole('tutor')) {
    $query->whereHas('tutors', function ($q) use ($user) {
        $q->where('users.id', $user->id)
        ->where('tutor_courses.is_owner', true); // comentar para que colaboren todos
    });
    

    }
    if ($request->has('username') && $user->hasRole('admin')) {
    $query->whereHas('tutors', function ($q) use ($request) {
        $q->where('username', $request->query('username'))
          ->where('tutor_courses.is_owner', true); // comentar para que colaboren todos
    });
    }


    // 🔎 Búsqueda por título o descripción
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // 🎛️ Filtros opcionales
    if (!empty($filters)) {
        $query->when(isset($filters['enabled']), fn($q) => $q->where('enabled', $filters['enabled']));
        $query->when(isset($filters['private']), fn($q) => $q->where('private', $filters['private']));
        $query->when(isset($filters['difficulty_id']), fn($q) => $q->where('difficulty_id', $filters['difficulty_id']));
    }

    // 🧠 Relaciones necesarias y métricas resumidas
    $courses = $query->with([
        'miniature:id,course_id,url',
        'categories:id,name',
        'tutors:id,name,lastname',
        'difficulty:id,name'
    ])
    ->withCount(['modules', 'allComments', 'savedCourses', 'registrations'])
    ->withSum('ratingCourses as total_stars', 'stars')
    ->orderBy('created_at', 'desc')
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
        'difficulty_id' => 'required|exists:difficulties,id',
        'private' => 'sometimes|boolean',
    ]);

    $data = array_merge($validated, [
        'enabled' => false,
        'private' => $validated['private'] ?? false,
    ]);

    $course = Course::create($data);

    // Asociar tutor creador como propietario
    if (Auth::user()->hasRole('tutor')) {
        $course->tutors()->attach(Auth::id(), ['is_owner' => true]);
    } elseif (Auth::user()->hasRole('admin')) {
        $course->tutors()->attach(Auth::id(), ['is_owner' => true]);
    }

    // Recargar relaciones necesarias para construir la misma estructura que index()
    $course->load([
        'miniature:id,course_id,url',
        'categories:id,name',
        'tutors:id,name,lastname',
        'difficulty:id,name'
    ])
    ->loadCount(['modules', 'allComments', 'savedCourses', 'registrations'])
    ->loadSum('ratingCourses as total_stars', 'stars');

    return response()->json([
        'message' => 'Curso creado exitosamente',
        'course' => [
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
        ]
    ], 201);
    }
    public function show(Course $course): JsonResponse
    {
        $this->authorize('viewHidden', $course);

        $course->load([
        'miniature:id,course_id,url',
        'careers:id,name',
        'categories:id,name',
        'difficulty:id,name',
        ]);

        return response()->json([
        'message' => 'Curso encontrado',
        'course'  => $course
    ]);
    }
/** Límites de relaciones */
    public int $maxCategories = 4;
    public int $maxCareers    = 2;

    /** Config de imagen */
    public array $allowedImageExtensions = ['jpg','png','gif'];
    public int   $maxImageSizeMb = 10; // 10MB
    public function update(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            // Campos base
            'title'         => 'sometimes|required|string|max:255',
            'description'   => 'sometimes|required|string',

            // -> usar IN en vez de boolean por form-data
            'private'       => 'sometimes|required|in:1,0,true,false,on,off,yes,no',
            'enabled'       => 'sometimes|in:1,0,true,false,on,off,yes,no',

            'difficulty_id' => 'sometimes|required|exists:difficulties,id',
            'code'          => ['sometimes','nullable','string', Rule::unique('courses','code')->ignore($course->id)],

            // Relaciones
            'categories'           => 'sometimes|array',
            'categories.*'         => 'nullable',
            'categories.*.id'      => 'required_without:categories.*|integer|exists:categories,id',
            'categories.*.order'   => 'nullable|integer|min:1',

            'careers'      => 'sometimes|array',
            'careers.*'    => 'integer|exists:careers,id',

            // Miniatura (archivo) -> multipart/form-data
            'miniature'    => [
                'sometimes',
                'file',
                'mimes:' . implode(',', $this->allowedImageExtensions),
                'max:' . ($this->maxImageSizeMb * 1024), // en KB
            ],
        ]);

        // ---- Normalización booleans para form-data ----
        foreach (['private','enabled'] as $flag) {
            if ($request->has($flag)) {
                $validated[$flag] = filter_var(
                    $request->input($flag),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );
            }
        }

        // ---- Normalización + límites ----
        $maxCategories = $this->maxCategories;
        $maxCareers    = $this->maxCareers;

        // categories -> ['id'=>X,'order'=>Y]
        $rawCategories  = $request->has('categories') ? ($validated['categories'] ?? []) : null;
        $normCategories = null;
        if ($rawCategories !== null) {
            $seen = [];
            $normCategories = [];
            $i = 1;
            foreach ($rawCategories as $item) {
                if (is_array($item)) {
                    $id    = $item['id'] ?? null;
                    $order = array_key_exists('order', $item) ? (int)$item['order'] : $i;
                } else {
                    $id    = $item;
                    $order = $i;
                }
                if ($id && !in_array($id, $seen, true)) {
                    $normCategories[] = ['id' => (int)$id, 'order' => $order > 0 ? $order : $i];
                    $seen[] = (int)$id;
                    $i++;
                }
            }
            if (count($normCategories) > $maxCategories) {
                throw ValidationException::withMessages([
                    'categories' => ['Solo se permiten ' . $maxCategories . ' categorías por curso.']
                ]);
            }
        }

        // careers: únicos + límite
        $normCareers = null;
        if ($request->has('careers')) {
            $normCareers = array_values(array_unique(array_map('intval', $validated['careers'] ?? [])));
            if (count($normCareers) > $maxCareers) {
                throw ValidationException::withMessages([
                    'careers' => ['Solo se permiten hasta ' . $maxCareers . ' carreras por curso.']
                ]);
            }
        }

        try {
            DB::transaction(function () use ($request, $course, $validated, $normCategories, $normCareers) {

                // 1) Actualizar campos base enviados
                $toUpdate = collect($validated)->only([
                    'title','description','private','enabled','difficulty_id','code'
                ])->toArray();

                $course->update($toUpdate);

                // 2) Categorías
                if ($normCategories !== null) {
                    usort($normCategories, fn($a,$b) => $a['order'] <=> $b['order']);
                    $payload = [];
                    $seq = 1;
                    foreach ($normCategories as $nc) {
                        $payload[$nc['id']] = ['order' => $seq++];
                    }
                    $course->categories()->sync($payload);
                }

                // 3) Carreras
                if ($normCareers !== null) {
                    $course->careers()->sync($normCareers);
                }

                // 4) Miniatura (archivo -> Cloudinary)
                if ($request->hasFile('miniature')) {
                    $file = $request->file('miniature');

                    $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
                    $upload = $cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'folder'        => "miniatures",
                            'public_id'     => "curso/{$course->id}",
                            'overwrite'     => true,
                            'resource_type' => 'image',
                            'transformation' => [
                                ['quality' => 'auto:good'],
                                ['fetch_format' => 'auto'],
                            ],
                        ]
                    );

                    $secureUrl = $upload['secure_url'] ?? null;
                    if ($secureUrl) {
                        $course->miniature()->updateOrCreate([], ['url' => $secureUrl]);
                    } else {
                        throw new \RuntimeException('No se pudo obtener la URL de Cloudinary.');
                    }
                }
            });

            $course->load([
                'categories' => function ($q) {
                    $q->withPivot('order')->orderBy('category_courses.order');
                },
                'careers',
                'miniature',
                'difficulty',
            ]);

            return response()->json([
                'message' => 'Curso actualizado',
                'course'  => $course
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error actualizando curso: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'No se pudo actualizar el curso.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    public function generateCode(): JsonResponse
    {

        $newCode = Course::generateUniqueCode();

        return response()->json([
            
            'message' => 'Código del curso generado',
            'code' => $newCode
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

    public function archived(Course $course): JsonResponse
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
/* $this->authorize('viewPortfolio', $user); */
    public function showOwner(string $username): JsonResponse
    {
    $targetUser = User::where('username', $username)
        ->with([
            'educationalUser.career',
            'educationalUser.sede.educationalUnit',
            'tutoredCourses' => fn ($query) =>
                $query->where('enabled', true)->with(['difficulty', 'categories'])
        ])
        ->firstOrFail();

    // 🔐 Verifica la autorización
    $this->authorize('viewOwner', $targetUser);

    $educationalUser = $targetUser->educationalUser;

    return response()->json([
        'message' => 'Portafolio cargado correctamente.',
        'portfolio' => [
            'name' => $targetUser->name,
            'lastname' => $targetUser->lastname,
            'username' => $targetUser->username,
            'email' => $targetUser->email,
            'profile_picture_url' => $targetUser->profile_picture_url,
            'joined_at' => Carbon::parse($targetUser->created_at)->locale('es')->translatedFormat('d M Y'),
            'career' => $educationalUser?->career ?? null,
            'sede' => $educationalUser?->sede ? [
                'id' => $educationalUser->sede->id,
                'province' => $educationalUser->sede->province,
                'canton' => $educationalUser->sede->canton,
                'educational_unit' => $educationalUser->sede->educationalUnit ?? null
            ] : null,
            'active_courses_count' => $targetUser->tutoredCourses->count(),
            'role' => $targetUser->getRoleNames()[0]
        ]
    ]);
    }

    
}
