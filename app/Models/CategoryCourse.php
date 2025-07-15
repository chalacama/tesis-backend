<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryCourse extends Model
{
    protected $fillable = [
        'name',
        'description',
        'order',
        'category_id',
        'course_id'
];
}
