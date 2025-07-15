<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use Illuminate\Support\Str;
class StartController extends Controller
{    
    private function formatCourses($courses)
    {
        return $courses->map(function ($course) {
            // Format essential course data
            $formatted = [
                'id' => $course->id,
                'title' => $course->title,
                'description' => Str::limit($course->description, 200), // Limit description for UX
                'created_at' => $course->created_at
                    ? \Carbon\Carbon::parse($course->created_at)->locale('es')->isoFormat('D MMM YYYY')
                    : null,
                'is_certified' => $course->certified ? $course->certified->is_certified : false,
                'thumbnails' => $course->miniatures->sortBy('order')->map(fn($miniature) => [
                    'url' => $course->miniatures->where('deleted_at', null)->value('url'), // Only non-deleted miniatures, ordered
                    'order' => $miniature->order
                ])->values(),
                'tutor' => $this->getTutorData($course),
                'category' => $course->categories->sortBy('pivot.order')->first() 
                    ? [
                        'id' => $course->categories->sortBy('pivot.order')->first()->id,
                        'name' => $course->categories->sortBy('pivot.order')->first()->name
                    ] 
                    : null,
                'careers' => $course->careers->map(fn($career) => [
                    'id' => $career->id,
                    'logo_url' => $career->url_logo // For career logo icons
                ])->values(),
                'difficulty' => $course->difficulty ? [
                    'id' => $course->difficulty->id,
                    'name' => $course->difficulty->name
                ] : null,
                'registrations_count' => $course->registrations_count ?? 0,
                'saved_courses_count' => $course->saved_courses_count ?? 0,
                'average_rating' => $course->ratingCourses->avg('stars') ?? 0 // For UX
            ];

            return $formatted;
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
            'tutors:id,name,lastname', // Only necessary tutor fields
            'categories:id,name,category_courses.order', // Include pivot order for sorting
            'miniatures:id,course_id,url,order,deleted_at', // Include order and deleted_at for sorting/filtering
            'careers:id,url_logo', // For career logo icons
            'difficulty:id,name', // Difficulty details
            'ratingCourses:course_id,stars' // For average rating
        ];
    }

    public function topPopularCourses()
    { 
    $courses = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount('registrations')
        ->withCount('savedCourses')
        ->orderByDesc(DB::raw('registrations_count + saved_courses_count'))
        ->take(5)
        ->get();
        return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
    }
    
    
    public function topBestRatedCourses()
    {
    $courses = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount('registrations')
        ->withCount('savedCourses')
        ->withSum('ratingCourses as total_stars', 'stars')
        ->orderByDesc('total_stars')
        ->take(5)
        ->get();

    return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
    }
    
    public function topUpdatedCourses()
{
    $courses = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount('registrations')
        ->withCount('savedCourses')
        ->orderByDesc('updated_at')
        ->take(5)
        ->get();

    return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
}
public function topCreatedCourses()
{
    $courses = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount('registrations')
        ->withCount('savedCourses')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();

    return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
}
public function recommendCoursesByUserInterest($userId)
{
    // Obtener las categorías de interés del usuario
    $categoryIds = DB::table('user_category_interests')
        ->where('user_id', $userId)
        ->pluck('category_id')
        ->toArray();

    // Si el usuario no tiene intereses, retornar vacío
    if (empty($categoryIds)) {
        return response()->json(['courses' => []]);
    }

    // Buscar cursos que tengan al menos una de esas categorías
    $courses = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->whereHas('categories', function($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        })
        ->withCount('registrations')
        ->withCount('savedCourses')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();

    return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
}



}

