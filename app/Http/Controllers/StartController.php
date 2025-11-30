<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Career;
use App\Models\Suggestion;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StartController extends Controller
{
    use HandlesAuthorization, AuthorizesRequests;

    /* ===================== UTILIDADES ===================== */

    private function normalize(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return $text;
    }

    private function like(string $text): string
    {
        return '%'.mb_strtolower($this->normalize($text)).'%';
    }

    private function getCourseWithRelations(): array
{
    return [
        'tutors:id,name,lastname,profile_picture_url,username',
        'miniature:id,course_id,url',
        // Solo lo necesario para is_saved / is_registered
        'savedCourses:course_id,user_id',
        'registrations:course_id,user_id',
        'modules:id,course_id,order',
        'modules.chapters:id,module_id,order',
        'modules.chapters.learningContent:id,chapter_id,url',
    ];
}


    private function getTutorData($course)
    {
        $owner = $course->tutors->where('pivot.is_owner', true)->first();
        return $owner
            ? [
                'id' => $owner->id,
                'name' => $owner->name . ' ' . $owner->lastname,
                'username' => $owner->username,
                'profile_picture_url' => $owner->profile_picture_url ?? null
            ]
            : [
                'id' => null,
                'name' => null,
                'username' => null,
                'profile_picture_url' => null
            ];
    }

    private function formatCourses($courses, ?User $user)
{
    return $courses->map(function ($course) use ($user) {
        $firstModule          = $course->modules->sortBy('order')->first();
        $firstChapter         = $firstModule ? $firstModule->chapters->sortBy('order')->first() : null;
        $firstLearningContent = $firstChapter ? $firstChapter->learningContent : null;

        return [
            'id'          => $course->id,
            'title'       => $course->title,
            'description' => \Illuminate\Support\Str::limit($course->description, 200),
            'created_at'  => $course->created_at
                ? \Carbon\Carbon::parse($course->created_at)->locale('es')->isoFormat('D MMM YYYY')
                : null,

            'thumbnail_url' => $course->miniature
                ? $course->miniature->url
                : null,

            'tutor' => $this->getTutorData($course),

            // Dejamos solo lo que sí necesitas
            'registrations_count' => $course->registrations_count ?? 0,

            'is_saved' => $user
                ? $course->savedCourses->where('user_id', $user->id)->isNotEmpty()
                : false,

            'is_registered' => $user
                ? $course->registrations->where('user_id', $user->id)->isNotEmpty()
                : false,

            'first_learning_content_url' => $firstLearningContent
                ? $firstLearningContent->url
                : null,
        ];
    });
}


    /* ===================== SÚPER FILTRO ===================== */

    public function getCoursesByFilter(Request $request): JsonResponse
{
    $user    = Auth::user();
    $filter  = $request->query('filter', 'all');
    $perPage = (int) $request->query('per_page', 6);
    $page    = (int) $request->query('page', 1);
    $term    = $this->normalize($request->query('q', ''));

    $query = Course::query()
        ->select('courses.*')
        ->with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount(['registrations', 'savedCourses'])
        ->withSum('ratingCourses as total_stars', 'stars');

    // === Búsqueda con relevancia ===
    if ($term !== '') {
        $like = $this->like($term);

        $relevanceSub = DB::table('courses as c')
            ->selectRaw("
                c.id as course_id,
                (CASE WHEN LOWER(c.title) LIKE ? THEN 5 ELSE 0 END)
              + (CASE WHEN EXISTS(
                    SELECT 1
                    FROM tutor_courses tc_owner
                    JOIN users owners ON owners.id = tc_owner.user_id
                    WHERE tc_owner.course_id = c.id
                      AND tc_owner.is_owner = 1
                      AND (
                          LOWER(CONCAT_WS(' ', owners.name, owners.lastname)) LIKE ?
                          OR LOWER(owners.username) LIKE ?
                      )
                ) THEN 4 ELSE 0 END)
              + (CASE WHEN EXISTS(
                    SELECT 1
                    FROM category_courses cc
                    JOIN categories cat ON cat.id = cc.category_id
                    WHERE cc.course_id = c.id
                      AND LOWER(cat.name) LIKE ?
                ) THEN 3 ELSE 0 END)
              + (CASE WHEN EXISTS(
                    SELECT 1
                    FROM career_courses cac
                    JOIN careers car ON car.id = cac.career_id
                    WHERE cac.course_id = c.id
                      AND LOWER(car.name) LIKE ?
                ) THEN 3 ELSE 0 END)
              AS relevance
            ", [$like, $like, $like, $like, $like])
            ->where('c.enabled', 1)
            ->whereNull('c.deleted_at')
            ->having('relevance', '>', 0)
            ->orderByDesc('relevance');

        $query
            ->joinSub($relevanceSub, 'sr', 'sr.course_id', '=', 'courses.id')
            ->addSelect(DB::raw('sr.relevance'))
            ->orderByDesc('sr.relevance');
    } else {
        // === Filtros clásicos del home ===

        // Recomendados por categorías
        if ($filter === 'recommended' && $user) {
            $recommendedCategories = DB::table('registrations as r')
                ->join('category_courses as cc', 'r.course_id', '=', 'cc.course_id')
                ->join('categories as c', 'cc.category_id', '=', 'c.id')
                ->select('c.id', DB::raw('SUM(CASE WHEN cc.order = 1 THEN 2 ELSE 1 END) as interest_score'))
                ->where('r.user_id', $user->id)
                ->groupBy('c.id')
                ->orderByDesc('interest_score')
                ->limit(5)
                ->pluck('c.id')
                ->toArray();

            if (empty($recommendedCategories)) {
                return response()->json([
                    'courses'      => [],
                    'has_more'     => false,
                    'current_page' => $page,
                ]);
            }

            $query->whereHas('categories', function ($q) use ($recommendedCategories) {
                $q->whereIn('categories.id', $recommendedCategories);
            })->orderByDesc('created_at');
        }

        // Mejor valorados
        if ($filter === 'best_rated') {
            $query->orderByDesc('total_stars');
        }

        // Populares (registros + guardados)
        if ($filter === 'popular') {
            $query->orderByDesc(DB::raw('registrations_count + saved_courses_count'));
        }

        // Actualizados
        if ($filter === 'updated') {
            $query->orderByDesc('updated_at');
        }

        // Más recientes
        if ($filter === 'created') {
            $query->orderByDesc('created_at');
        }

        // Home "all" priorizando categorías favoritas
        if ($filter === 'all' && $user) {
            $recommendedCategories = DB::table('registrations as r')
                ->join('category_courses as cc', 'r.course_id', '=', 'cc.course_id')
                ->join('categories as c', 'cc.category_id', '=', 'c.id')
                ->select('c.id', DB::raw('SUM(CASE WHEN cc.order = 1 THEN 2 ELSE 1 END) as interest_score'))
                ->where('r.user_id', $user->id)
                ->groupBy('c.id')
                ->orderByDesc('interest_score')
                ->limit(5)
                ->pluck('c.id')
                ->toArray();

            $ids = implode(',', $recommendedCategories ?: [0]);

            $query->orderByRaw("
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM category_courses cc 
                        WHERE cc.course_id = courses.id AND cc.category_id IN ($ids)
                    ) THEN 1 ELSE 2
                END
            ")
            ->orderByDesc('total_stars')
            ->orderByDesc(DB::raw('registrations_count + saved_courses_count'));
        }
    }

    // Fallback general
    $query->orderByDesc('total_stars')
          ->orderByDesc(DB::raw('registrations_count + saved_courses_count'))
          ->orderByDesc('created_at');

    // Paginación manual (lookahead)
    $courses = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
    $hasMore = $courses->count() > $perPage;

    if ($hasMore) {
        $courses = $courses->slice(0, $perPage);
    }

    return response()->json([
        'courses'      => $this->formatCourses($courses, $user),
        'has_more'     => $hasMore,
        'current_page' => $page,
    ]);
}

public function getPortfolioByFilter(Request $request): JsonResponse
{
    $authUser = Auth::user();

    $perPage  = (int) $request->query('per_page', 6);
    $page     = (int) $request->query('page', 1);
    $filter   = $request->query('filter', 'created'); // popular | best_rated | created
    $term     = $this->normalize($request->query('q', ''));
    $type     = $request->query('type', 'courses');    // courses | collaborations
    $username = $this->normalize($request->query('username', ''));

    // Permitir username con o sin @ (ej: @bautista69 o bautista69)
    if ($username !== '') {
        $username = ltrim($username, '@');
    }

    // 1) Resolver usuario del portafolio
    $portfolioUser = null;

    if ($username !== '') {
        $portfolioUser = User::where('username', $username)->first();
    } elseif ($authUser) {
        $portfolioUser = $authUser;
    }

    if (!$portfolioUser) {
        return response()->json([
            'courses'      => [],
            'has_more'     => false,
            'current_page' => $page,
        ]);
    }

    // 2) Query base: solo cursos donde es tutor (dueño o colaborador)
    $query = Course::query()
        ->select('courses.*')
        ->with($this->getCourseWithRelations())
        ->where('courses.enabled', true)
        ->withCount(['registrations', 'savedCourses'])
        ->withSum('ratingCourses as total_stars', 'stars')
        ->whereHas('tutors', function ($q) use ($portfolioUser, $type) {
            $q->where('users.id', $portfolioUser->id);

            if ($type === 'courses') {
                // cursos donde es dueño
                $q->where('tutor_courses.is_owner', true);
            } elseif ($type === 'collaborations') {
                // cursos donde es colaborador
                $q->where('tutor_courses.is_owner', false);
            }
        });

    // 3) Search dentro del portafolio (por nombre del curso)
    if ($term !== '') {
        $like = $this->like($term);
        $query->whereRaw('LOWER(courses.title) LIKE ?', [$like]);
    }

    // 4) Orden según filtro
    switch ($filter) {
        case 'popular':
            $query->orderByDesc(DB::raw('registrations_count + saved_courses_count'))
                  ->orderByDesc('total_stars')
                  ->orderByDesc('courses.created_at');
            break;

        case 'best_rated':
            $query->orderByDesc('total_stars')
                  ->orderByDesc(DB::raw('registrations_count + saved_courses_count'))
                  ->orderByDesc('courses.created_at');
            break;

        case 'created':
        default:
            // más recientes
            $query->orderByDesc('courses.created_at')
                  ->orderByDesc('total_stars')
                  ->orderByDesc(DB::raw('registrations_count + saved_courses_count'));
            break;
    }

    // 5) Paginación manual (lookahead)
    $courses = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
    $hasMore = $courses->count() > $perPage;

    if ($hasMore) {
        $courses = $courses->slice(0, $perPage);
    }

    return response()->json([
        'courses'      => $this->formatCourses($courses, $authUser),
        'has_more'     => $hasMore,
        'current_page' => $page,
    ]);
}



    /* ===================== SUGERENCIAS / HISTORIAL ===================== */

    public function getSuggestionByFilter(Request $request): JsonResponse
    {
        $user = Auth::user();
        $term = $this->normalize($request->query('q', ''));
        $limit = (int) $request->query('limit', 10);

        // 1) Sin escribir: devolver historial reciente
        if ($term === '') {
            $history = Suggestion::where('user_id', $user->id)
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get()
                ->map(fn($s) => ['text' => $s->texto, 'is_history' => true])
                ->values();

            return response()->json(['suggestions' => $history]);
        }

        $like = $this->like($term);

        // 2) Historial que coincide (forzamos base Collection con toBase)
$historyMatches = Suggestion::where('user_id', $user->id)
    ->whereRaw('LOWER(texto) LIKE ?', [$like])
    ->orderByDesc('updated_at')
    ->limit($limit)
    ->get()
    ->map(fn($s) => ['text' => $s->texto, 'is_history' => true])
    ->values()
    ->toBase(); // <--- IMPORTANTE

// 3) Sugerencia general (no historial): títulos, categorías, carreras, dueños
$titles = Course::where('enabled', true)
    ->whereRaw('LOWER(title) LIKE ?', [$like])
    ->limit($limit)->pluck('title')->toArray();

$categories = Category::whereRaw('LOWER(name) LIKE ?', [$like])
    ->limit(5)->pluck('name')->toArray();

$careers = Career::whereRaw('LOWER(name) LIKE ?', [$like])
    ->limit(5)->pluck('name')->toArray();

$owners = DB::table('users as o')
    ->join('tutor_courses as tc', 'tc.user_id', '=', 'o.id')
    ->where('tc.is_owner', 1)
    ->where(function ($w) use ($like) {
        $w->whereRaw('LOWER(o.username) LIKE ?', [$like])
          ->orWhereRaw('LOWER(CONCAT_WS(" ", o.name, o.lastname)) LIKE ?', [$like]);
    })
    ->selectRaw('DISTINCT TRIM(CASE WHEN COALESCE(o.username,"") <> "" THEN o.username ELSE CONCAT(o.name," ",o.lastname) END) as text')
    ->limit(5)
    ->pluck('text')
    ->toArray();

$general = collect(array_merge($titles, $categories, $careers, $owners))
    ->filter()
    ->map(fn($t) => ['text' => $this->normalize($t), 'is_history' => false])
    ->values(); // base Collection

// 4) Merge sin duplicados (case-insensitive) y tope por límite
$suggestions = $historyMatches
    ->merge($general)          // ambas ya son base Collection
    ->unique(function ($item) {
        return mb_strtolower($item['text']);
    })
    ->take($limit)
    ->values();

return response()->json(['suggestions' => $suggestions]);

    }

    public function updateSuggestion(Request $request): JsonResponse
    {
        $request->validate(['text' => 'required|string|max:255']);
        $user = Auth::user();
        $text = $this->normalize($request->input('text'));

        $s = Suggestion::firstOrCreate(
            ['user_id' => $user->id, 'texto' => $text],
            ['searched' => 0]
        );
        $s->increment('searched'); // +1 búsqueda
        $s->touch();               // actualiza updated_at

        return response()->json(['ok' => true]);
    }
}
