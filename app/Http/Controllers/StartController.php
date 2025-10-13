<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\MiniatureCourse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class StartController extends Controller
{    
    
   use HandlesAuthorization, AuthorizesRequests;


    private function formatCourses($courses, ?User $user)
    {
        return $courses->map(function ($course) use ($user) {
            $firstModule = $course->modules->sortBy('order')->first();
            $firstChapter = $firstModule ? $firstModule->chapters->sortBy('order')->first() : null;
            $firstLearningContent = $firstChapter ? $firstChapter->learningContent : null;

            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => Str::limit($course->description, 200),
                'created_at' => $course->created_at
                    ? \Carbon\Carbon::parse($course->created_at)->locale('es')->isoFormat('D MMM YYYY')
                    : null,
                'thumbnail_url' => $course->miniature && !$course->miniature->trashed()
                    ? $course->miniature->url
                    : null,
                'tutor' => $this->getTutorData($course),
                'category' => $course->categories->sortBy('pivot.order')->first()
                    ? [
                        'id' => $course->categories->sortBy('pivot.order')->first()->id,
                        'name' => $course->categories->sortBy('pivot.order')->first()->name
                    ]
                    : null,
                'careers' => $course->careers->map(fn($career) => [
                    'id' => $career->id,
                    'logo_url' => $career->url_logo
                ])->values(),
                'difficulty' => $course->difficulty ? [
                    'id' => $course->difficulty->id,
                    'name' => $course->difficulty->name
                ] : null,
                'registrations_count' => $course->registrations_count ?? 0,
                'saved_courses_count' => $course->saved_courses_count ?? 0,
                'average_rating' => $course->ratingCourses->avg('stars') ?? 0,
                'is_saved' => $user ? $course->savedCourses->where('user_id', $user->id)->isNotEmpty() : false,
                'is_registered' => $user ? $course->registrations->where('user_id', $user->id)->isNotEmpty() : false,
                'first_learning_content_url' => $firstLearningContent ? $firstLearningContent->url : null
            ];
        });
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

    private function getCourseWithRelations()
    {
        return [
            'tutors:id,name,lastname,profile_picture_url,username',
            'categories:id,name,category_courses.order',
            'miniature:id,course_id,url,deleted_at',
            'careers:id,url_logo',
            'difficulty:id,name',
            'ratingCourses:course_id,stars',
            'savedCourses:course_id,user_id',
            'registrations:course_id,user_id',
            'modules:id,course_id,order',
            'modules.chapters:id,module_id,order',
            'modules.chapters.learningContent:id,chapter_id,url'
        ];
    }

    public function getCoursesByFilter(Request $request)
    {
    $this->authorize('viewAny', Course::class);
    $user = Auth::user();
    $filter = $request->query('filter', 'all');
    $perPage = $request->query('per_page', 6);
    $page = $request->query('page', 1);

    $query = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount(['registrations', 'savedCourses'])
        ->withSum('ratingCourses as total_stars', 'stars');

    if ($filter === 'recommended') {
    $recommendedCategories = DB::table('registrations as r')
        ->join('category_courses as cc', 'r.course_id', '=', 'cc.course_id')
        ->join('categories as c', 'cc.category_id', '=', 'c.id')
        ->select(
            'c.id',
            DB::raw('SUM(CASE WHEN cc.order = 1 THEN 2 ELSE 1 END) as interest_score')
        )
        ->where('r.user_id', $user->id)
        // ->where('r.annulment', false)
        ->groupBy('c.id')
        ->orderByDesc('interest_score')
        ->limit(5)
        ->pluck('c.id') // Solo nos interesan los IDs para el filtro
        ->toArray();

        if (empty($recommendedCategories)) {
        return response()->json([
            'courses' => [],
            'has_more' => false,
            'current_page' => (int)$page
        ]);
        }

        $query->whereHas('categories', function ($q) use ($recommendedCategories) {
        $q->whereIn('categories.id', $recommendedCategories);
        });

    $query->orderByDesc('created_at');
    }


    if ($filter === 'best_rated') {
        $query->orderByDesc('total_stars');
    }

    if ($filter === 'popular') {
        $query->orderByDesc(DB::raw('registrations_count + saved_courses_count'));
    }

    if ($filter === 'updated') {
        $query->orderByDesc('updated_at');
    }

    if ($filter === 'created') {
        $query->orderByDesc('created_at');
    }

    if ($filter === 'all') {
    // Paso 1: Obtener categorías más relevantes para el usuario
    $recommendedCategories = DB::table('registrations as r')
        ->join('category_courses as cc', 'r.course_id', '=', 'cc.course_id')
        ->join('categories as c', 'cc.category_id', '=', 'c.id')
        ->select(
            'c.id',
            DB::raw('SUM(CASE WHEN cc.order = 1 THEN 2 ELSE 1 END) as interest_score')
        )
        ->where('r.user_id', $user->id)
        // ->where('r.annulment', false)
        ->groupBy('c.id')
        ->orderByDesc('interest_score')
        ->limit(5)
        ->pluck('c.id')
        ->toArray();

    // Paso 2: Aplicar ordenamiento personalizado
    $query->orderByRaw("
        CASE 
            WHEN EXISTS (
                SELECT 1 
                FROM category_courses cc 
                WHERE cc.course_id = courses.id 
                AND cc.category_id IN (" . implode(',', $recommendedCategories ?: [0]) . ")
            ) THEN 1
            ELSE 2
        END
    ")
    ->orderByDesc('total_stars')
    ->orderByDesc(DB::raw('registrations_count + saved_courses_count'));
    }


    $courses = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();

    // Detectar si hay más cursos disponibles
    $hasMore = $courses->count() > $perPage;
    if ($hasMore) {
        $courses = $courses->slice(0, $perPage);
    }

    return response()->json([
        'courses' => $this->formatCourses($courses, $user),
        'has_more' => $hasMore,
        'current_page' => (int)$page
    ]);
    }


}

