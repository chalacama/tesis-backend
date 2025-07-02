<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Course;

class StartController extends Controller
{
    
    
    public function topPopularCourses()
    {
    $courses = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount('registrations')
        ->withCount('savedCourses')
        ->orderByDesc(DB::raw('registrations_count + saved_courses_count'))
        ->get();
        return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
    }
    
    private function formatCourses($courses)
{
    return $courses->map(function ($course) {
        $course->published_at_formatted = $course->published_at
            ? \Carbon\Carbon::parse($course->published_at)->locale('es')->isoFormat('D MMM YYYY')
            : null;
        $course->created_at_formatted = $course->created_at
            ? \Carbon\Carbon::parse($course->created_at)->locale('es')->isoFormat('D MMM YYYY')
            : null;
        $course->is_certified = $course->certified ? $course->certified->is_certified : false;

        // Tutor principal y carrera
        if ($course->tutorCourses && $course->tutorCourses->count() > 0) {
            $tutor = $course->tutorCourses->first()->user;
            $course->tutor_name = $tutor ? $tutor->name . ' ' . $tutor->lastname : 'ESPAM MFL';
            $course->tutor_career_info = ($tutor && $tutor->userInformation && $tutor->userInformation->career)
    ? [
        'id' => $tutor->userInformation->career->id,
        'name' => $tutor->userInformation->career->name,
        'url_logo' => $tutor->userInformation->career->url_logo
    ]
    : null;
        } else {
            $course->tutor_name = 'ESPAM MFL';
            $course->tutor_career = null;
        }

        // Miniatura principal habilitada
        $course->miniature_url = ($course->miniatures && $course->miniatures->count() > 0)
            ? $course->miniatures->first()->url
            : null;

        // Unir id y name de categorías en un solo array de objetos
        $course->categorias = $course->categories
            ? $course->categories->map(function($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name
                ];
            })->values()
            : [];
        //Elimina los sobrantes
        unset($course->certified, $course->tutorCourses, $course->miniatures,$course->categories);
         
        return $course;
    });
}

    private function getCourseWithRelations()
{
    return [
        'certified:id,course_id,is_certified',
        'tutorCourses' => function($q) {
            $q->where('enabled', true);
        },
        'tutorCourses.user.userInformation.career', // Trae la carrera del tutor
        'categories'=> function($q) {
            $q->where('enabled', true);
        }
        ,
        'miniatures' => function($q) {
            $q->where('enabled', true);
        }
    ];
}
    public function topBestRatedCourses()
    {
    $courses = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount('registrations')
        ->withCount('savedCourses')
        ->withSum('ratingCourses as total_stars', 'stars')
        ->orderByDesc('total_stars')
        ->get();

    return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
    }
    
    public function topPublishCourses()
{
    $courses = Course::with($this->getCourseWithRelations())
        ->where('enabled', true)
        ->withCount('registrations')
        ->withCount('savedCourses')
        ->orderByDesc('published_at')
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
        ->get();

    return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
}
public function recommendCoursesByUserInterest($userId)
{
    // Obtener las categorías de interés del usuario
    $categoryIds = \DB::table('user_category_interests')
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
        ->get();

    return response()->json([
        'courses' => $this->formatCourses($courses)
    ]);
}



}

