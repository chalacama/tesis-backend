<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
class Category extends Model
{
    protected $fillable = ['name'];
    /**
     * Relación muchos a muchos con Course a través de category_courses.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'category_courses');
    }
    
    /**
     * Relación uno a muchos con UserCategoryInterest.
     */
    public function userCategoryInterests()
    {
        return $this->hasMany(UserCategoryInterest::class);
    }
}
