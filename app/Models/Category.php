<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * Relación muchos a muchos con Course a través de category_courses.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'category_courses');
    }
}
