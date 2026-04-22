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
use App\Models\ContentView;
use App\Models\UserCategoryInterest;
use App\Models\EducationalUser;
use App\Models\Difficulty;

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
                'name' => $owner->name,
                'lastname' => $owner->lastname,
                'username' => $owner->username,
                'profile_picture_url' => $owner->profile_picture_url ?? null
            ]
            : [
                'id' => null,
                'name' => 'Digi',
                'username' => 'Mentor',
                'profile_picture_url' => null
            ];
    }

    private function formatCourses($courses, ?User $user, array $recentlyViewedIds = [])
{
    return $courses->map(function ($course) use ($user, $recentlyViewedIds) {
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

            'recently_viewed' => in_array($course->id, $recentlyViewedIds),

            'first_learning_content_url' => $firstLearningContent
                ? $firstLearningContent->url
                : null,
        ];
    });
}


/* ===================== FILTROS DEL HOME ===================== */

    public function getCoursesByFilter(Request $request): JsonResponse
    {
        $user    = Auth::user();
        $filter  = $request->query('filter', 'all');
        $perPage = (int) $request->query('per_page', 6);
        $page    = (int) $request->query('page', 1);

        $recentlyViewedIds = $user ? ContentView::select('modules.course_id', DB::raw('MAX(content_views.updated_at) as latest_view'))
            ->join('learning_contents', 'content_views.learning_content_id', '=', 'learning_contents.id')
            ->join('chapters', 'learning_contents.chapter_id', '=', 'chapters.id')
            ->join('modules', 'chapters.module_id', '=', 'modules.id')
            ->where('content_views.user_id', $user->id)
            ->groupBy('modules.course_id')
            ->orderByDesc('latest_view')
            ->pluck('course_id')
            ->toArray() : [];

        if ($filter === 'all') {
            if ($page == 1 && $user) {
                $recentIds = ContentView::select('modules.course_id', DB::raw('MAX(content_views.updated_at) as latest_view'))
                    ->join('learning_contents', 'content_views.learning_content_id', '=', 'learning_contents.id')
                    ->join('chapters', 'learning_contents.chapter_id', '=', 'chapters.id')
                    ->join('modules', 'chapters.module_id', '=', 'modules.id')
                    ->where('content_views.user_id', $user->id)
                    ->groupBy('modules.course_id')
                    ->orderByDesc('latest_view')
                    ->limit(3)
                    ->pluck('course_id')
                    ->toArray();

                $recentCourses = collect();
                if (!empty($recentIds)) {
                    $recentCourses = Course::whereIn('id', $recentIds)
                        ->with($this->getCourseWithRelations())
                        ->withCount(['registrations', 'savedCourses'])
                        ->withSum('ratingCourses as total_stars', 'stars')
                        ->get()
                        ->sortBy(function($c) use ($recentIds) {
                            return array_search($c->id, $recentIds);
                        });
                }
                $remaining = $perPage - $recentCourses->count();
                $randomCourses = collect();
                if ($remaining > 0) {
                    $randomCourses = Course::where('enabled', true)
                        ->whereNotIn('id', $recentIds)
                        ->inRandomOrder()
                        ->take($remaining + 1)
                        ->with($this->getCourseWithRelations())
                        ->withCount(['registrations', 'savedCourses'])
                        ->withSum('ratingCourses as total_stars', 'stars')
                        ->get();
                    $hasMore = $randomCourses->count() > $remaining;
                    if ($hasMore) {
                        $randomCourses = $randomCourses->take($remaining);
                    }
                } else {
                    $hasMore = false;
                }
                $courses = $recentCourses->merge($randomCourses);
            } else {
                $courses = Course::where('enabled', true)
                    ->inRandomOrder()
                    ->skip(($page - 1) * $perPage)
                    ->take($perPage + 1)
                    ->with($this->getCourseWithRelations())
                    ->withCount(['registrations', 'savedCourses'])
                    ->withSum('ratingCourses as total_stars', 'stars')
                    ->get();
                $hasMore = $courses->count() > $perPage;
                if ($hasMore) {
                    $courses = $courses->take($perPage);
                }
            }
        } elseif ($filter === 'recommended' && $user) {
            $query = Course::query()
                ->select('courses.*')
                ->with($this->getCourseWithRelations())
                ->where('enabled', true)
                ->withCount(['registrations', 'savedCourses'])
                ->withSum('ratingCourses as total_stars', 'stars')
                ->whereDoesntHave('registrations', fn($q) => $q->where('user_id', $user->id));

            $userCategories = UserCategoryInterest::where('user_id', $user->id)->pluck('category_id')->toArray();
            if (!empty($userCategories)) {
                $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $userCategories));
            }

            $userCareer = EducationalUser::where('user_id', $user->id)->first()?->career_id;
            if ($userCareer) {
                $query->whereHas('careers', fn($q) => $q->where('careers.id', $userCareer));
            }

            $query->orderByDesc('created_at');

            $courses = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
            $hasMore = $courses->count() > $perPage;
            if ($hasMore) {
                $courses = $courses->slice(0, $perPage);
            }
        } elseif ($filter === 'created') {
            $query = Course::query()
                ->select('courses.*')
                ->with($this->getCourseWithRelations())
                ->where('enabled', true)
                ->withCount(['registrations', 'savedCourses'])
                ->withSum('ratingCourses as total_stars', 'stars')
                ->orderByDesc('created_at');

            $courses = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
            $hasMore = $courses->count() > $perPage;
            if ($hasMore) {
                $courses = $courses->slice(0, $perPage);
            }
        } elseif ($filter === 'best_rated') {
            $query = Course::query()
                ->select('courses.*')
                ->with($this->getCourseWithRelations())
                ->where('enabled', true)
                ->withCount(['registrations', 'savedCourses'])
                ->withSum('ratingCourses as total_stars', 'stars')
                ->orderByDesc('total_stars');

            $courses = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
            $hasMore = $courses->count() > $perPage;
            if ($hasMore) {
                $courses = $courses->slice(0, $perPage);
            }
        } elseif ($filter === 'popular') {
            $query = Course::query()
                ->select('courses.*')
                ->with($this->getCourseWithRelations())
                ->where('enabled', true)
                ->withCount(['registrations', 'savedCourses'])
                ->withSum('ratingCourses as total_stars', 'stars')
                ->orderByDesc(DB::raw('registrations_count + saved_courses_count'));

            $courses = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
            $hasMore = $courses->count() > $perPage;
            if ($hasMore) {
                $courses = $courses->slice(0, $perPage);
            }
        } else {
            $courses = collect();
            $hasMore = false;
        }

        return response()->json([
            'courses'      => $this->formatCourses($courses, $user, $recentlyViewedIds),
            'has_more'     => $hasMore,
            'current_page' => $page,
        ]);
    }

public function getPortfolioByFilter(Request $request): JsonResponse
{
    $authUser = Auth::user();
    $recentlyViewedIds = $authUser ? ContentView::select('modules.course_id', DB::raw('MAX(content_views.updated_at) as latest_view'))
        ->join('learning_contents', 'content_views.learning_content_id', '=', 'learning_contents.id')
        ->join('chapters', 'learning_contents.chapter_id', '=', 'chapters.id')
        ->join('modules', 'chapters.module_id', '=', 'modules.id')
        ->where('content_views.user_id', $authUser->id)
        ->groupBy('modules.course_id')
        ->orderByDesc('latest_view')
        ->pluck('course_id')
        ->toArray() : [];

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
        'courses'      => $this->formatCourses($courses, $authUser, $recentlyViewedIds),
        'has_more'     => $hasMore,
        'current_page' => $page,
    ]);
}

/* ===================== RESULTADOS DE BÚSQUEDA ===================== */

    public function searchCourses(Request $request): JsonResponse
    {
        $user    = Auth::user();
        $term    = $this->normalize($request->query('q', ''));
        $type    = $request->query('type', 'title');
        $perPage = (int) $request->query('per_page', 6);
        $page    = (int) $request->query('page', 1);

        $validTypes = ['title', 'category', 'career', 'difficulty', 'tutor'];
        if (!in_array($type, $validTypes)) {
            $type = 'title';
        }

        $recentlyViewedIds = $user ? ContentView::select('modules.course_id', DB::raw('MAX(content_views.updated_at) as latest_view'))
            ->join('learning_contents', 'content_views.learning_content_id', '=', 'learning_contents.id')
            ->join('chapters', 'learning_contents.chapter_id', '=', 'chapters.id')
            ->join('modules', 'chapters.module_id', '=', 'modules.id')
            ->where('content_views.user_id', $user->id)
            ->groupBy('modules.course_id')
            ->pluck('course_id')
            ->toArray() : [];

        $entities = [];
        $coursesFormatted = [];
        $hasMore = false;

        if ($term !== '') {
            $like = $this->like($term);

            $query = Course::query()
                ->select('courses.*')
                ->with($this->getCourseWithRelations())
                ->where('courses.enabled', true)
                ->whereNull('courses.deleted_at')
                ->withCount(['registrations', 'savedCourses'])
                ->withSum('ratingCourses as total_stars', 'stars');

            switch ($type) {
                case 'title':
                    // Para el título usamos el mismo motor de relevancia global (tal como lo solicitaste)
                    $relevanceSub = DB::table('courses as c')
                        ->selectRaw("
                            c.id as course_id,
                            (CASE WHEN LOWER(c.title) LIKE ? THEN 5 ELSE 0 END)
                          + (CASE WHEN EXISTS(
                                SELECT 1 FROM tutor_courses tc_owner
                                JOIN users owners ON owners.id = tc_owner.user_id
                                WHERE tc_owner.course_id = c.id AND tc_owner.is_owner = 1
                                  AND (LOWER(CONCAT_WS(' ', owners.name, owners.lastname)) LIKE ? OR LOWER(owners.username) LIKE ?)
                            ) THEN 4 ELSE 0 END)
                          + (CASE WHEN EXISTS(
                                SELECT 1 FROM category_courses cc
                                JOIN categories cat ON cat.id = cc.category_id
                                WHERE cc.course_id = c.id AND LOWER(cat.name) LIKE ?
                            ) THEN 3 ELSE 0 END)
                          + (CASE WHEN EXISTS(
                                SELECT 1 FROM career_courses cac
                                JOIN careers car ON car.id = cac.career_id
                                WHERE cac.course_id = c.id AND LOWER(car.name) LIKE ?
                            ) THEN 3 ELSE 0 END)
                          AS relevance
                        ", [$like, $like, $like, $like, $like])
                        ->where('c.enabled', 1)
                        ->whereNull('c.deleted_at')
                        ->having('relevance', '>', 0);

                    $query->joinSub($relevanceSub, 'sr', 'sr.course_id', '=', 'courses.id')
                          ->addSelect(DB::raw('sr.relevance'))
                          ->orderByDesc('sr.relevance');
                    break;

                case 'category':
                    if ($page === 1) {
                        $entities = Category::whereRaw('LOWER(name) LIKE ?', [$like])
                            ->select('id', 'name')
                            ->limit(5)->get();
                    }
                    $query->whereHas('categories', fn($q) => $q->whereRaw('LOWER(categories.name) LIKE ?', [$like]));
                    break;

                case 'career':
                    if ($page === 1) {
                        $entities = Career::whereRaw('LOWER(name) LIKE ?', [$like])
                            ->select('id', 'name', 'url_logo') // Ojo: Asegúrate que url_logo exista en tu tabla careers
                            ->limit(5)->get();
                    }
                    $query->whereHas('careers', fn($q) => $q->whereRaw('LOWER(careers.name) LIKE ?', [$like]));
                    break;

                case 'difficulty':
                    if ($page === 1) {
                        // Asumiendo que tu tabla y modelo se llaman Difficulty
                        $entities = Difficulty::whereRaw('LOWER(name) LIKE ?', [$like])
                            ->select('id', 'name')
                            ->limit(5)->get();
                    }
                    // Ojo: Asegúrate de que la relación en tu modelo Course se llame "difficulty" 
                    // o ajusta este "whereHas" según tu base de datos (por ejemplo, si es una columna directa puedes usar where('difficulty_id', ...))
                    $query->whereHas('difficulty', fn($q) => $q->whereRaw('LOWER(difficulties.name) LIKE ?', [$like]));
                    break;

                case 'tutor':
                    if ($page === 1) {
                        $entities = DB::table('users as o')
                            ->join('tutor_courses as tc', 'tc.user_id', '=', 'o.id')
                            ->where('tc.is_owner', 1)
                            ->where(function ($w) use ($like) {
                                $w->whereRaw('LOWER(o.username) LIKE ?', [$like])
                                  ->orWhereRaw('LOWER(o.email) LIKE ?', [$like])
                                  ->orWhereRaw('LOWER(CONCAT_WS(" ", o.name, o.lastname)) LIKE ?', [$like]);
                            })
                            ->selectRaw('DISTINCT o.id, o.username, o.name, o.lastname, o.profile_picture_url')
                            ->limit(5)
                            ->get();
                    }
                    // Ojo: Asegúrate de que la relación se llame "tutors" en el modelo Course
                    $query->whereHas('tutors', function($q) use ($like) {
                        $q->where('tutor_courses.is_owner', 1)
                          ->where(function ($w) use ($like) {
                              $w->whereRaw('LOWER(users.username) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(users.email) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(CONCAT_WS(" ", users.name, users.lastname)) LIKE ?', [$like]);
                          });
                    });
                    break;
            }

            $courses = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
            $hasMore = $courses->count() > $perPage;
            
            if ($hasMore) {
                $courses = $courses->slice(0, $perPage);
            }
            
            $coursesFormatted = $this->formatCourses($courses, $user, $recentlyViewedIds);
        }

        return response()->json([
            'entities'     => $entities, // Saldrá vacío si es 'title' o si page > 1 (ahorra datos)
            'courses'      => $coursesFormatted,
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
        $type = $request->query('type', 'title');

        $validTypes = ['title', 'category', 'career', 'difficulty', 'tutor'];
        if (!in_array($type, $validTypes)) {
            $type = 'title';
        }

        // 1) Sin escribir (q vacío): devolver todo el historial reciente sin importar el type
        if ($term === '') {
            $history = Suggestion::where('user_id', $user->id)
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get()
                ->map(fn($s) => [
                    'text' => $s->texto,
                    'is_history' => true,
                    'search_type' => $s->search_type,
                    'entity_id' => $s->entity_id,
                    'searched' => $s->searched
                ])
                ->values();

            return response()->json(['suggestions' => $history]);
        }

        $like = $this->like($term);

        // 2) Historial que coincide con type y texto
        $historyMatches = Suggestion::where('user_id', $user->id)
            ->where('search_type', $type)
            ->whereRaw('LOWER(texto) LIKE ?', [$like])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn($s) => [
                'text' => $s->texto,
                'is_history' => true,
                'search_type' => $s->search_type,
                'entity_id' => $s->entity_id,
                'searched' => $s->searched
            ])
            ->values()
            ->toBase();

        // 3) Sugerencias nuevas según type
        $newSuggestions = collect();
        switch ($type) {
            case 'title':
                $newSuggestions = Course::where('enabled', true)
                    ->whereRaw('LOWER(title) LIKE ?', [$like])
                    ->select('id', 'title as text')
                    ->limit($limit)
                    ->get()
                    ->map(fn($c) => [
                        'text' => $c->text,
                        'is_history' => false,
                        'search_type' => 'title',
                        'entity_id' => $c->id,
                        'searched' => 0
                    ]);
                break;
            case 'category':
                $newSuggestions = Category::whereRaw('LOWER(name) LIKE ?', [$like])
                    ->select('id', 'name as text')
                    ->limit($limit)
                    ->get()
                    ->map(fn($c) => [
                        'text' => $c->text,
                        'is_history' => false,
                        'search_type' => 'category',
                        'entity_id' => $c->id,
                        'searched' => 0
                    ]);
                break;
            case 'career':
                $newSuggestions = Career::whereRaw('LOWER(name) LIKE ?', [$like])
                    ->select('id', 'name as text')
                    ->limit($limit)
                    ->get()
                    ->map(fn($c) => [
                        'text' => $c->text,
                        'is_history' => false,
                        'search_type' => 'career',
                        'entity_id' => $c->id,
                        'searched' => 0
                    ]);
                break;
            case 'difficulty':
                $newSuggestions = Difficulty::whereRaw('LOWER(name) LIKE ?', [$like])
                    ->select('id', 'name as text')
                    ->limit($limit)
                    ->get()
                    ->map(fn($d) => [
                        'text' => $d->text,
                        'is_history' => false,
                        'search_type' => 'difficulty',
                        'entity_id' => $d->id,
                        'searched' => 0
                    ]);
                break;
            case 'tutor':
                $newSuggestions = DB::table('users as o')
                    ->join('tutor_courses as tc', 'tc.user_id', '=', 'o.id')
                    ->where('tc.is_owner', 1)
                    ->where(function ($w) use ($like) {
                        $w->whereRaw('LOWER(o.username) LIKE ?', [$like])
                          ->orWhereRaw('LOWER(o.email) LIKE ?', [$like])
                          ->orWhereRaw('LOWER(CONCAT_WS(" ", o.name, o.lastname)) LIKE ?', [$like]);
                    })
                    ->selectRaw('DISTINCT o.id, TRIM(CASE WHEN COALESCE(o.username,"") <> "" THEN o.username ELSE CONCAT(o.name," ",o.lastname) END) as text')
                    ->limit($limit)
                    ->get()
                    ->map(fn($t) => [
                        'text' => $t->text,
                        'is_history' => false,
                        'search_type' => 'tutor',
                        'entity_id' => $t->id,
                        'searched' => 0
                    ]);
                break;
        }

        // 4) Merge sin duplicados y tope por límite
        $suggestions = $historyMatches
            ->merge($newSuggestions)
            ->unique(function ($item) {
                return mb_strtolower($item['text']);
            })
            ->take($limit)
            ->values();

        return response()->json(['suggestions' => $suggestions]);
    }

    public function updateSuggestion(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:255',
            'search_type' => 'required|string|in:title,category,career,difficulty,tutor',
            'entity_id' => 'nullable|integer'
        ]);

        $user = Auth::user();
        $text = $this->normalize($request->input('text'));
        $searchType = $request->input('search_type');
        $entityId = $request->input('entity_id');

        $suggestion = Suggestion::where('user_id', $user->id)
            ->whereRaw('LOWER(texto) = ?', [mb_strtolower($text)])
            ->first();

        if ($suggestion) {
            $suggestion->search_type = $searchType;
            $suggestion->entity_id = $entityId;
            $suggestion->increment('searched');
            $suggestion->touch();
        } else {
            $count = Suggestion::where('user_id', $user->id)->count();
            
            if ($count >= 10) {
                Suggestion::where('user_id', $user->id)
                    ->orderBy('updated_at', 'asc')
                    ->first()
                    ->delete();
            }

            Suggestion::create([
                'user_id' => $user->id,
                'texto' => $text,
                'search_type' => $searchType,
                'entity_id' => $entityId,
                'searched' => 1
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
