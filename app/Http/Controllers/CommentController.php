<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\LikeComment;
use App\Models\ReplyComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Notifications\CourseCommentedNotification;
use App\Notifications\CommentRepliedNotification;

class CommentController extends Controller
{
     use AuthorizesRequests;
    public function index(Request $request, Course $course)
{
    $this->authorize('viewRegistered', $course);

    $userId  = optional($request->user())->id;
    $perPage = (int) $request->integer('per_page', 20);
    $perPage = max(5, min($perPage, 50));

    // Dueño del curso (para liked_by_owner)
    $owner = $course->owner()
        ->select('users.id', 'users.username', 'users.name', 'users.lastname', 'users.profile_picture_url')
        ->first();
    $ownerId = optional($owner)->id;

    // 1) Total de comentarios del curso (incluye raíz + todas las respuestas)
    $totalComments = Comment::query()
        ->where('commentable_type', Course::class)
        ->where('commentable_id', $course->id)
        ->count();

    // 2) Página de comentarios raíz (parent_id = null)
    $query = $course->comments() // asumiendo scope que filtra parent_id = null
        ->with(['user:id,username,name,lastname,profile_picture_url'])
        ->withCount([
            'replies as replies_count',         // hijos directos
            'likeComments as likes_count',

            'likeComments as liked_by_me' => function ($q) use ($userId) {
                $userId ? $q->where('user_id', $userId) : $q->whereRaw('1=0');
            },

            'likeComments as liked_by_owner' => function ($q) use ($ownerId) {
                $ownerId ? $q->where('user_id', $ownerId) : $q->whereRaw('1=0');
            },
        ])
        ->orderByDesc('replies_count')
        ->orderByDesc('likes_count')
        ->orderByDesc('id');

    $comments = $query->simplePaginate($perPage);

    // 3) Para los comentarios de ESTA página, calcular total de descendientes (todas las respuestas)
    $rootIds = $comments->getCollection()->pluck('id')->values()->all();

    $allRepliesByRoot = [];
    if (!empty($rootIds)) {
        // Requiere MySQL 8+ o PostgreSQL. Si usas otra base, avísame y te paso fallback.
        $placeholders = implode(',', array_fill(0, count($rootIds), '?'));

        // CTE: sembramos múltiples raíces; para cada root_id, contamos todo su subárbol.
        $sql = "
            WITH RECURSIVE tree AS (
                SELECT id, parent_id, id AS root_id
                FROM comments
                WHERE id IN ($placeholders)

                UNION ALL

                SELECT c.id, c.parent_id, t.root_id
                FROM comments c
                JOIN tree t ON c.parent_id = t.id
                WHERE c.commentable_type = ? AND c.commentable_id = ?
            )
            SELECT root_id, COUNT(*) - 1 AS all_replies_count
            FROM tree
            GROUP BY root_id
        ";

        $bindings = array_merge($rootIds, [Course::class, $course->id]);
        $rows = DB::select($sql, $bindings);

        foreach ($rows as $r) {
            $allRepliesByRoot[(int)$r->root_id] = (int)$r->all_replies_count;
        }
    }

    // 4) Inyecta 'all_replies_count' y preserva 'replies_count' (directo)
    $comments->getCollection()->transform(function ($c) use ($allRepliesByRoot) {
        $c->setAttribute('all_replies_count', (int)($allRepliesByRoot[$c->id] ?? 0));
        // opcional: también puedes exponer un alias si te gusta otro nombre
        return $c;
    });

    // 5) Payload del owner para UI
    $ownerPayload = $owner ? [
        'id'       => $owner->id,
        'username' => $owner->username,
        'name'     => $owner->name,
        'lastname' => $owner->lastname,
        'avatar'   => $owner->profile_picture_url,
    ] : null;

    // 6) Devuelve colección + metadatos fuera de data
    return CommentResource::collection($comments)
        ->additional([
            'ok'                => true,
            'owner'             => $ownerPayload,
            'total_comments'    => $totalComments,  // ⬅️ TOTAL del curso (raíz + replies)
            'total_roots'       => $course->comments()->count(), // opcional (solo raíz)
        ]);
}


    /**
     * GET /courses/{course}/comments/{comment}/replies
     * Lista respuestas de un comentario con paginación (scroll en las respuestas).
     */
    // App/Http/Controllers/CommentController.php

public function replies(Request $request, Course $course, Comment $comment)
{
    $this->authorize('viewRegistered', $course);

    // Asegura que el comentario pertenece al curso
    if ($comment->commentable_type !== Course::class || (int)$comment->commentable_id !== (int)$course->id) {
        abort(404, 'Comentario no pertenece a este curso.');
    }

    $userId  = optional($request->user())->id;
    $perPage = (int) $request->integer('per_page', 20);
    $perPage = max(5, min($perPage, 50));

    // Dueño del curso (para liked_by_owner)
    $owner   = $course->owner()->select('users.id')->first();
    $ownerId = optional($owner)->id;

    // === 1) CTE recursivo: obtener TODOS los descendientes del comentario raíz (excluyendo el root)
    //     Traemos también la profundidad relativa (0 = root, 1 = hijos directos, etc.)
    $connection = DB::getDriverName(); // 'mysql' | 'pgsql' | ...
    if (! in_array($connection, ['mysql','pgsql'])) {
        abort(500, 'La base no soporta CTE recursivo con este ejemplo. Avísame y te paso fallback.');
    }

    $bindings = [
        'root_id'           => $comment->id,
        'ctype'             => Course::class,
        'cid'               => $course->id,
        'ctype2'            => Course::class,
        'cid2'              => $course->id,
        'root_id_again'     => $comment->id,
    ];

    $cteSql = "
        WITH RECURSIVE tree AS (
            SELECT id, parent_id, commentable_id, commentable_type, 0 AS depth
            FROM comments
            WHERE id = :root_id

            UNION ALL

            SELECT c.id, c.parent_id, c.commentable_id, c.commentable_type, tree.depth + 1
            FROM comments c
            JOIN tree ON c.parent_id = tree.id
            WHERE c.commentable_type = :ctype AND c.commentable_id = :cid
        )
        SELECT id, parent_id, depth
        FROM tree
        WHERE id <> :root_id_again
          AND commentable_type = :ctype2
          AND commentable_id   = :cid2
    ";

    $descendants = collect(DB::select($cteSql, $bindings)); // [ {id, parent_id, depth}, ...]

    if ($descendants->isEmpty()) {
        // Estructura de paginación vacía pero consistente
        return CommentResource::collection(
            Comment::query()->whereRaw('1=0')->simplePaginate($perPage)
        )->additional([
            'ok'                => true,
            'parent_comment_id' => $comment->id,
            'depth'             => 'all',
        ]);
    }

    $ids       = $descendants->pluck('id')->all();
    $depthById = $descendants->keyBy('id')->map->depth; // [id => depth]

    // === 2) Consulta Eloquent: trae modelos + counts + usuario del padre (para reply_to)
    $query = Comment::query()
        ->whereIn('id', $ids)
        ->with([
            'user:id,username,name,lastname,profile_picture_url',
            'parent:id,user_id,parent_id',
            'parent.user:id,username,name,lastname,profile_picture_url',
        ])
        ->withCount([
            'replies as replies_count',
            'likeComments as likes_count',

            'likeComments as liked_by_me' => function ($q) use ($userId) {
                $userId ? $q->where('user_id', $userId) : $q->whereRaw('1 = 0');
            },

            'likeComments as liked_by_owner' => function ($q) use ($ownerId) {
                $ownerId ? $q->where('user_id', $ownerId) : $q->whereRaw('1 = 0');
            },
        ])
        // Orden: primero por likes, luego por fecha (id desc). Ajusta si quieres.
        ->orderByDesc('likes_count')
        ->orderByDesc('id');

    $replies = $query->simplePaginate($perPage);

    // === 3) Adjunta la profundidad a cada modelo (para que el Resource pueda incluirla)
    $replies->getCollection()->each(function ($r) use ($depthById) {
        $r->setAttribute('depth', (int) ($depthById[$r->id] ?? 1));
    });

    return CommentResource::collection($replies)
        ->additional([
            'ok'                => true,
            'parent_comment_id' => $comment->id,
            'depth'             => 'all', // documenta que devuelves todos los niveles
        ]);
}


    public function store(Request $request, Course $course)
{
    // Autorización de acceso al curso (tu política actual)
    $this->authorize('viewRegistered', $course);

    // Debe estar autenticado
    $user = $request->user();
    if (! $user) {
        return response()->json([
            'ok' => false,
            'message' => 'Debes iniciar sesión para comentar.'
        ], 401);
    }

    // Reglas: texto obligatorio; parent_id opcional pero, si viene, debe pertenecer al mismo curso y no estar borrado
    $data = $request->validate([
        'texto' => ['required','string','min:1','max:3000'],
        'parent_id' => [
            'nullable','integer',
            Rule::exists('comments','id')
                ->where(function($q) use ($course) {
                    $q->where('commentable_type', Course::class)
                      ->where('commentable_id', $course->id);
                }),
        ],
    ]);

    // Regla de negocio: solo registrados (o dueños/colaboradores) pueden comentar
    $isTutorOrOwner = $course->tutors()->where('users.id', $user->id)->exists(); // dueños y colaboradores
    $isRegistered   = $course->registrations()->where('user_id', $user->id)->exists();

    if (! $isTutorOrOwner && ! $isRegistered) {
        return response()->json([
            'ok' => false,
            'message' => 'Solo usuarios registrados en el curso pueden comentar.'
        ], 403);
    }

    // Creamos comentario
    $comment = Comment::create([
        'user_id'         => $user->id,
        'texto'           => $data['texto'],
        'parent_id'       => $data['parent_id'] ?? null,
        'commentable_type'=> Course::class,
        'commentable_id'  => $course->id,
    ]);

    // Cargamos relaciones y contadores para responder igual que en index/replies
    $ownerId = optional(
        $course->owner()->select('users.id')->first()
    )->id;

    $comment->load([
        'user:id,username,name,lastname,profile_picture_url',
        'parent:id,user_id,parent_id',
        'parent.user:id,username,name,lastname,profile_picture_url',
    ])->loadCount([
        // hijos directos
        'replies as replies_count',
        // likes totales
        'likeComments as likes_count',
        // liked por mí
        'likeComments as liked_by_me' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        },
        // liked por el dueño del curso
        'likeComments as liked_by_owner' => function ($q) use ($ownerId) {
            $ownerId ? $q->where('user_id', $ownerId) : $q->whereRaw('1=0');
        },
    ]);

    // Opcional: si quieres devolver también 'all_replies_count' (subárbol completo) recién creado (será 0),
    // puedes setearlo explícitamente para mantener consistencia con index():
    $comment->setAttribute('all_replies_count', 0);

    // 🔔 Notificaciones
    try {
        // Caso 1: comentario raíz sobre el curso (sin parent_id)
        if (is_null($comment->parent_id)) {

            // Dueño del curso (si existe)
            $courseOwner = $course->owner()->first(); // asumiendo relación owner() -> User

            if ($courseOwner && $courseOwner->id !== $user->id) {
                $courseOwner->notify(
                    new CourseCommentedNotification($course, $comment, $user)
                );
            }

        // Caso 2: respuesta a un comentario existente
        } else {
            $parentAuthor = optional($comment->parent)->user;

            if ($parentAuthor && $parentAuthor->id !== $user->id) {
                $parentAuthor->notify(
                    new CommentRepliedNotification($course, $comment->parent, $comment, $user)
                );
            }
        }
    } catch (\Throwable $e) {
        // No rompas la creación del comentario si falla una notificación
        Log::warning('Error enviando notificación de comentario', [
            'error'       => $e->getMessage(),
            'comment_id'  => $comment->id ?? null,
            'course_id'   => $course->id ?? null,
        ]);
    }

    return CommentResource::make($comment)
        ->additional([
            'ok' => true,
            'message' => 'Comentario creado correctamente.',
        ]);
}


}
