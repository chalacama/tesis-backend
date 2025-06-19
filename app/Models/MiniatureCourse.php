<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniatureCourse extends Model
{
    protected $fillable = [
        'course_id',
        'url',
    ];

    /**
     * Relación: una miniatura pertenece a un curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
