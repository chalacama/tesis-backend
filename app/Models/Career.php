<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $fillable = [
        'name',
        'max_semesters',
        'url_logo'
    ];

    /**
     * Relación uno a muchos con UserInformation.
     */
    public function userInformations()
    {
        return $this->hasMany(UserInformation::class, 'career_id');
    }
    public function courses()
{
    return $this->belongsToMany(Course::class, 'career_courses');
}

    /**
     * Relación uno a muchos con CareerSede.
     */
    public function careerSedes()
    {
        return $this->hasMany(CareerSede::class);
    }

    /**
     * Relación uno a muchos con CareerCourse.
     */
    public function careerCourses()
    {
        return $this->hasMany(CareerCourse::class);
    }
    
}
