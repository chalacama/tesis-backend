<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Course;
class MiniatureCourse extends Model
{
    
    protected $fillable = [
        'course_id',
        'url'
    ];

    /**
     * Relación: una miniatura pertenece a un curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
