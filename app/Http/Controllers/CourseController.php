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
use App\Models\TypeThumbnail;
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
use App\Models\Registration;
use App\Notifications\CourseUpdatedNotification;

class CourseController extends Controller
{

    use AuthorizesRequests;
    public function index(Request $request): JsonResponse
{
    $this->authorize('viewAnyHidden', Course::class);

    $perPage = (int) $request->query('per_page', 10);
    $search  = $request->query('search');
    $filters = (array) $request->query('filters', []);
    $user    = Auth::user();

    // Base query: solo columnas necesarias del curso
    $query = Course::query()
        ->withTrashed()
        ->select([
            'id',
            'title',
            'description',
            'private',
            'code',
            'enabled',
            'difficulty_id',
            'deleted_at',
            'created_at',
        ]);

    // 🔐 Filtro para tutores: solo ver cursos donde colaboran (aquí solo dueño)
    if ($user->hasRole('tutor')) {
        $query->whereHas('tutors', function ($q) use ($user) {
            $q->where('users.id', $user->id)
              ->where('tutor_courses.is_owner', true); // comenta si quieres todos
        });
    }

    // 🔐 Filtro por username (solo admin) → dueño del curso
    if ($request->has('username') && $user->hasRole('admin')) {
        $query->whereHas('owner', function ($q) use ($request) {
            $q->where('username', $request->query('username'));
        });
    }

    // 🔎 Búsqueda por título o descripción
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // 🎛️ Filtros opcionales
    if (!empty($filters)) {
        // enabled (boolean)
        $query->when(isset($filters['enabled']), function ($q) use ($filters) {
            $q->where('enabled', (bool) $filters['enabled']);
        });

        // private (boolean)
        $query->when(isset($filters['private']), function ($q) use ($filters) {
            $q->where('private', (bool) $filters['private']);
        });

        // dificultad
        $query->when(isset($filters['difficulty_id']), function ($q) use ($filters) {
            $q->where('difficulty_id', $filters['difficulty_id']);
        });

        // ✅ Filtrar por categoría
        // filters[category_id]=ID
        $query->when(isset($filters['category_id']), function ($q) use ($filters) {
            $q->whereHas('categories', function ($q2) use ($filters) {
                $q2->where('categories.id', $filters['category_id']);
            });
        });

        // ✅ Filtrar por carrera
        // filters[career_id]=ID
        $query->when(isset($filters['career_id']), function ($q) use ($filters) {
            $q->whereHas('careers', function ($q2) use ($filters) {
                $q2->where('careers.id', $filters['career_id']);
            });
        });

        // ✅ NUEVO: filtrar por COLABORADOR (nombre+apellido o username)
        // filters[collaborator]=string
        $query->when(
            isset($filters['collaborator']) && trim($filters['collaborator']) !== '',
            function ($q) use ($filters) {
                $term = trim($filters['collaborator']);

                $q->whereHas('collaborators', function ($q2) use ($term) {
                    $q2->where(function ($qq) use ($term) {
                        // nombre + apellido juntos
                        $qq->whereRaw(
                            "CONCAT(users.name, ' ', COALESCE(users.lastname, '')) LIKE ?",
                            ["%{$term}%"]
                        )
                        // o por username
                        ->orWhere('users.username', 'like', "%{$term}%");
                    });
                });
            }
        );
    }

    // 🧠 Relaciones necesarias y métricas RESUMIDAS
    $courses = $query
        ->with([
            'miniature:id,course_id,url',
            'difficulty:id,name',
            // dueño del curso
            'owner:id,name,lastname,email,profile_picture_url,username',
            // colaboradores
            'collaborators:id,name,lastname,email,profile_picture_url,username',
        ])
        // Solo contadores que sí necesitas
        ->withCount([
            'savedCourses',
            'registrations',
        ])
        ->orderByDesc('created_at')
        ->paginate($perPage)
        ->through(function (Course $course) {
            // owner() es belongsToMany con is_owner = true → Collection
            $owner = $course->owner->first();

            return [
                'id'          => $course->id,
                'title'       => $course->title,
                'description' => $course->description,
                'private'     => $course->private,
                'code'        => $course->code,
                'enabled'     => $course->enabled,
                'deleted_at'  => $course->deleted_at,

                'saved_courses_count' => $course->saved_courses_count,
                'registrations_count' => $course->registrations_count,

                'miniature' => $course->miniature ? [
                    'id'  => $course->miniature->id,
                    'url' => $course->miniature->url,
                ] : null,

                'difficulty' => $course->difficulty ? [
                    'id'   => $course->difficulty->id,
                    'name' => $course->difficulty->name,
                ] : null,

                'creador' => $owner ? [
                    'id'                  => $owner->id,
                    'name'                => $owner->name,
                    'lastname'            => $owner->lastname,
                    'email'               => $owner->email,
                    'profile_picture_url' => $owner->profile_picture_url,
                    'username'            => $owner->username,
                ] : null,

                'colaboradores' => $course->collaborators
                    ->map(function (User $user) {
                        return [
                            'id'                  => $user->id,
                            'name'                => $user->name,
                            'lastname'            => $user->lastname,
                            'email'               => $user->email,
                            'profile_picture_url' => $user->profile_picture_url,
                            'username'            => $user->username,
                        ];
                    })
                    ->values(),
            ];
        });

    return response()->json([
        'courses' => $courses->items(),
        'pagination' => [
            'total'        => $courses->total(),
            'per_page'     => $courses->perPage(),
            'current_page' => $courses->currentPage(),
            'last_page'    => $courses->lastPage(),
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
        'miniature:id,course_id,url,width,height,aspect_ratio,size_bytes,type_thumbnail_id',
        'careers:id,name',
        'categories:id,name',
        'difficulty:id,name',
        ]);

        // Obtenemos todos los tipos de miniatura para el frontend
       /*  $typeThumbnails = TypeThumbnail::all(); */

        return response()->json([
        'message' => 'Curso encontrado',
        'course'  => $course
        ]);
    }
/** Límites de relaciones */
    public int $maxCategories = 4;
    public int $maxCareers    = 2;

    public function update(Request $request, Course $course): JsonResponse
{
    $this->authorize('update', $course);

    // Validación preliminar del tipo de miniatura
    $typeThumbnail = null;
    if ($request->has('type_thumbnail_id')) {
        $typeThumbnail = \App\Models\TypeThumbnail::where('id', $request->input('type_thumbnail_id'))
            ->where('enabled', true)
            ->first();
        if (!$typeThumbnail) {
            throw ValidationException::withMessages([
                'type_thumbnail_id' => 'Tipo de miniatura inválido o deshabilitado.'
            ]);
        }
    }

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

        // Miniatura
        'type_thumbnail_id' => 'sometimes|integer|exists:type_thumbnails,id',
        'url_miniature'     => 'sometimes|nullable|url',

        // Miniatura (archivo) -> multipart/form-data
        'miniature'    => [
            'sometimes',
            'file',
            'mimes:jpg,png,webp',
            $typeThumbnail && $typeThumbnail->max_size_bytes ? 'max:' . ($typeThumbnail->max_size_bytes / 1024) : '',
        ],

        // Flag explícito para eliminar miniatura
        'remove_miniature' => 'sometimes|in:1,0,true,false,on,off,yes,no',
    ]);

    // ---- Normalización booleans para form-data ----
    foreach (['private','enabled','remove_miniature'] as $flag) {
        if ($request->has($flag)) {
            $validated[$flag] = filter_var(
                $request->input($flag),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }
    }

    $removeMiniature = (bool)($validated['remove_miniature'] ?? false);

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
        $wasUpdated = false;

        DB::transaction(function () use ($request, $course, $validated, $normCategories, $normCareers, $removeMiniature, &$wasUpdated) {

            // 1) Actualizar campos base enviados
            $toUpdate = collect($validated)->only([
                'title','description','private','enabled','difficulty_id','code'
            ])->toArray();

            if (!empty($toUpdate)) {
                $course->update($toUpdate);
                $wasUpdated = $course->wasChanged() || $wasUpdated;
            }

            // 2) Categorías
            if ($normCategories !== null) {
                usort($normCategories, fn($a,$b) => $a['order'] <=> $b['order']);
                $payload = [];
                $seq = 1;
                foreach ($normCategories as $nc) {
                    $payload[$nc['id']] = ['order' => $seq++];
                }
                $course->categories()->sync($payload);
                $wasUpdated = true;
            }

            // 3) Carreras
            if ($normCareers !== null) {
                $course->careers()->sync($normCareers);
                $wasUpdated = true;
            }

            // 4) Miniatura
            $miniatureChanged = false;
            if ($removeMiniature) {
                $miniature = $course->miniature;
                if ($miniature) {
                    // Si era archive.image, eliminar archivo de GCS
                    if ($miniature->typeThumbnail && $miniature->typeThumbnail->name === 'archive.image') {
                        Storage::disk('gcs')->delete($miniature->url);
                    }
                    $miniature->delete();
                    $miniatureChanged = true;
                }
            } elseif ($request->hasFile('miniature')) {
                // Subir nuevo archivo (debe ser archive.image)
                if (!$typeThumbnail || $typeThumbnail->name !== 'archive.image') {
                    throw new \Exception('Para subir un archivo, type_thumbnail_id debe ser de tipo archive.image.');
                }
                $file = $request->file('miniature');
                $ext = $file->getClientOriginalExtension();
                $name = 'thumbnail_' . time() . '.' . $ext;
                $path = "courses/{$course->id}/thumbnail/{$name}";
                // Subir a GCS
                Storage::disk('gcs')->put($path, file_get_contents($file->getRealPath()));
                // Extraer metadatos
                $sizeBytes = $file->getSize();
                $imageInfo = getimagesize($file->getRealPath());
                $width = $imageInfo[0] ?? null;
                $height = $imageInfo[1] ?? null;
                $aspectRatio = null;
                if ($width && $height) {
                    $gcd = gmp_gcd($width, $height);
                    $aspectRatio = ($width / $gcd) . ':' . ($height / $gcd);
                }
                // Eliminar archivo anterior si existía y era archive.image
                $existingMiniature = $course->miniature;
                if ($existingMiniature && $existingMiniature->typeThumbnail && $existingMiniature->typeThumbnail->name === 'archive.image') {
                    Storage::disk('gcs')->delete($existingMiniature->url);
                }
                // Crear o actualizar
                $course->miniature()->updateOrCreate([], [
                    'url' => $path,
                    'name' => $name,
                    'size_bytes' => $sizeBytes,
                    'width' => $width,
                    'height' => $height,
                    'aspect_ratio' => $aspectRatio,
                    'type_thumbnail_id' => $typeThumbnail->id,
                ]);
                $miniatureChanged = true;
            } elseif ($request->has('url_miniature')) {
                // Guardar URL (debe ser link.image)
                if (!$typeThumbnail || $typeThumbnail->name !== 'link.image') {
                    throw new \Exception('Para guardar una URL, type_thumbnail_id debe ser de tipo link.image.');
                }
                $url = $request->input('url_miniature');
                // Eliminar archivo anterior si era archive.image
                $existingMiniature = $course->miniature;
                if ($existingMiniature && $existingMiniature->typeThumbnail && $existingMiniature->typeThumbnail->name === 'archive.image') {
                    Storage::disk('gcs')->delete($existingMiniature->url);
                }
                // Crear o actualizar
                $course->miniature()->updateOrCreate([], [
                    'url' => $url,
                    'name' => null,
                    'size_bytes' => null,
                    'width' => null,
                    'height' => null,
                    'aspect_ratio' => null,
                    'type_thumbnail_id' => $typeThumbnail->id,
                ]);
                $miniatureChanged = true;
            }
            // Si no se envía nada, no se toca la miniatura
            $wasUpdated = $wasUpdated || $miniatureChanged;
        });

        // 🔔 Si realmente hubo cambios, notificar a los estudiantes registrados
        if ($wasUpdated) {
            $this->notifyRegisteredUsersCourseUpdated($course, $request->user());
        }

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

/**
 * Notifica a todos los usuarios registrados en el curso
 * que el curso ha sido actualizado.
 *
 * SOLO los que estén registrados (tabla registrations).
 */
protected function notifyRegisteredUsersCourseUpdated(Course $course, ?User $actor = null): void
{
    // Obtenemos los IDs de usuarios registrados al curso
    $userIds = Registration::query()
        ->where('course_id', $course->id)
        ->pluck('user_id')
        ->unique()
        ->values()
        ->all();

    if (empty($userIds)) {
        return;
    }

    // Obtenemos los usuarios
    $students = User::query()
        ->whereIn('id', $userIds)
        ->get();

    foreach ($students as $student) {
        // Opcional: no notificar al mismo que editó si él también está registrado
        if ($actor && $actor->id === $student->id) {
            continue;
        }

        $student->notify(new CourseUpdatedNotification($course, $actor));
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

    // opcional: apagar el curso al archivarlo
    $course->enabled = false;
    $course->save();

    $course->delete();

    return response()->json(['message' => 'Curso enviado a papelería']);
}
    public function restore(string $courseId): JsonResponse
{
    $course = Course::withTrashed()->findOrFail($courseId);

    $this->authorize('restore', $course);

    if (!$course->trashed()) {
        return response()->json([
            'message' => 'El curso no está archivado.'
        ], 409);
    }

    $course->restore();

    // opcional: al restaurar, lo activas
    $course->enabled = false;
    $course->save();

    return response()->json([
        'message' => 'Curso restaurado (inactivo).',
        'course' => [
            'id' => $course->id,
            'enabled' => $course->enabled,
            'deleted_at' => $course->deleted_at,
        ],
    ]);
}

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

public function forceDestroy(string $courseId): JsonResponse
{
    set_time_limit(300);

    $course = Course::withTrashed()->findOrFail($courseId);

    $this->authorize('forceDelete', $course);

    // Seguridad: primero debe estar en papelería
    if (!$course->trashed()) {
        return response()->json([
            'message' => 'Para eliminar permanentemente primero archiva el curso (papelería).'
        ], 409);
    }

    // 1) Obtener SOLO capítulos que realmente tienen contenido de tipo "archivo"
    // (para no llamar a Cloudinary por cada capítulo innecesariamente)
    $chapterIdsWithFiles = DB::table('modules')
        ->join('chapters', 'chapters.module_id', '=', 'modules.id')
        ->join('learning_contents', 'learning_contents.chapter_id', '=', 'chapters.id')
        ->join('type_learning_contents', 'type_learning_contents.id', '=', 'learning_contents.type_content_id')
        ->where('modules.course_id', $course->id)
        ->whereRaw('LOWER(TRIM(type_learning_contents.name)) = ?', ['archivo'])
        ->pluck('chapters.id')
        ->unique()
        ->values()
        ->all();

    try {
        DB::beginTransaction();

        // 2) Limpieza de pivotes (por si NO hay cascade en pivotes)
        $course->categories()->detach();
        $course->careers()->detach();
        $course->tutors()->detach();

        // 3) Limpieza de tablas directas (por si alguna NO está en cascade)
        // (si tu BD ya tiene cascade, esto no estorba; evita errores por FK)
        $course->miniature()?->delete();
        $course->ratingCourses()?->delete();
        $course->registrations()?->delete();
        $course->savedCourses()?->delete();
        $course->invitations()?->delete();

        // Comentarios polimórficos (no siempre hay FK)
        DB::table('comments')
            ->where('commentable_type', Course::class)
            ->where('commentable_id', $course->id)
            ->delete();

        // 4) Eliminar curso permanente (tu cascade debe eliminar modules->chapters->learning_contents...)
        $course->forceDelete();

        DB::commit();

    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('Error force deleting course (DB)', [
            'course_id' => $courseId,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'No se pudo eliminar permanentemente el curso (DB).',
            'error'   => config('app.debug') ? $e->getMessage() : 'Error interno'
        ], 500);
    }

    // 5) Fuera de la transacción: borrar assets en Cloudinary (best-effort)
    // Si falla Cloudinary, NO revertimos DB (ya fue eliminado). Solo log.
    try {
        $this->deleteCourseCloudinaryAssetsByConvention($courseId, $chapterIdsWithFiles);
    } catch (\Throwable $e) {
        Log::warning('Force delete: Cloudinary cleanup failed', [
            'course_id' => $courseId,
            'error' => $e->getMessage(),
        ]);
    }

    return response()->json([
        'message' => 'Curso eliminado permanentemente.'
    ], 200);
}
private function deleteCourseCloudinaryAssetsByConvention(string $courseId, array $chapterIdsWithFiles): void
{
    $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));

    // A) Miniatura: folder miniatures + public_id curso/{id}
    // => publicId completo: miniatures/curso/{id}
    $this->destroyCloudinaryAnyType($cloudinary, "miniatures/curso/{$courseId}");

    // B) Archivos de capítulos: folder archives + public_id chapter/{chapterId}
    // => publicId completo: archives/chapter/{chapterId}
    foreach ($chapterIdsWithFiles as $chapterId) {
        $this->destroyCloudinaryAnyType($cloudinary, "archives/chapter/{$chapterId}");
    }
}

private function destroyCloudinaryAnyType(Cloudinary $cloudinary, string $publicId): void
{
    // Tus uploads son resource_type=auto.
    // Para borrar sin adivinar, intentamos en image/video/raw (best-effort).
    foreach (['image', 'video', 'raw'] as $type) {
        try {
            $cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => $type,
                'invalidate'    => true,
            ]);
        } catch (\Throwable $e) {
            // seguimos al siguiente type
        }
    }
}

}
