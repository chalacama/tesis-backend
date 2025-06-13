<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    /**
     * Relación uno a muchos con TutorCourse.
     */
    public function tutorCourses()
    {
        return $this->hasMany(TutorCourse::class);
    }

    /**
     * Relación muchos a muchos con User a través de tutor_courses (tutores del curso).
     */
    public function tutors()
    {
        return $this->belongsToMany(User::class, 'tutor_courses')
            ->withPivot('enabled', 'order')
            ->withTimestamps();
    }

    /**
     * Relación uno a muchos con RatingCourse.
     */
    public function ratingCourses()
    {
        return $this->hasMany(RatingCourse::class);
    }

    /**
     * Relación muchos a muchos con User a través de rating_courses (usuarios que calificaron).
     */
    public function usersRated()
    {
        return $this->belongsToMany(User::class, 'rating_courses')
            ->withPivot('stars')
            ->withTimestamps();
    }
     /**
     * Relación muchos a muchos con Category a través de category_courses.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_courses');
    }
}
