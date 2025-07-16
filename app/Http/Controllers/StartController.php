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
    /* public function __construct()
    {
        $this->authorizeResource(Course::class, 'course');
    } */
   use HandlesAuthorization, AuthorizesRequests;

    public function topPopularCourses()
    {
        $this->authorize('viewAny', Course::class);
        $user = Auth::user();

        $courses = Course::with($this->getCourseWithRelations())
            ->where('enabled', true)
            ->withCount(['registrations', 'savedCourses'])
            ->orderByDesc(DB::raw('registrations_count + saved_courses_count'))
            ->take(5)
            ->get();

        return response()->json([
            'courses' => $this->formatCourses($courses, $user)
        ]);
    }

    public function topBestRatedCourses()
    {
        $this->authorize('viewAny', Course::class);
        $user = Auth::user();

        $courses = Course::with($this->getCourseWithRelations())
            ->where('enabled', true)
            ->withCount(['registrations', 'savedCourses'])
            ->withSum('ratingCourses as total_stars', 'stars')
            ->orderByDesc('total_stars')
            ->take(5)
            ->get();

        return response()->json([
            'courses' => $this->formatCourses($courses, $user)
        ]);
    }

    public function topUpdatedCourses()
    {
        $this->authorize('viewAny', Course::class);
        $user = Auth::user();

        $courses = Course::with($this->getCourseWithRelations())
            ->where('enabled', true)
            ->withCount(['registrations', 'savedCourses'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        return response()->json([
            'courses' => $this->formatCourses($courses, $user)
        ]);
    }

    public function topCreatedCourses()
    {
        $this->authorize('viewAny', Course::class);
        $user = Auth::user();

        $courses = Course::with($this->getCourseWithRelations())
            ->where('enabled', true)
            ->withCount(['registrations', 'savedCourses'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return response()->json([
            'courses' => $this->formatCourses($courses, $user)
        ]);
    }

    public function recommendCoursesByUserInterest()
    {
        $this->authorize('viewAny', Course::class);
        $user = Auth::user();

        if (!$user) {
            return response()->json(['courses' => []]);
        }

        $categoryIds = DB::table('user_category_interests')
            ->where('user_id', $user->id)
            ->pluck('category_id')
            ->toArray();

        if (empty($categoryIds)) {
            return response()->json(['courses' => []]);
        }

        $courses = Course::with($this->getCourseWithRelations())
            ->where('enabled', true)
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->withCount(['registrations', 'savedCourses'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return response()->json([
            'courses' => $this->formatCourses($courses, $user)
        ]);
    }

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
                'is_certified' => $course->certified ? $course->certified->is_certified : false,
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
            ? ['name' => $owner->name . ' ' . $owner->lastname]
            : ['name' => 'ESPAM MFL'];
    }

    private function getCourseWithRelations()
    {
        return [
            'certified:id,course_id,is_certified',
            'tutors:id,name,lastname',
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


}

