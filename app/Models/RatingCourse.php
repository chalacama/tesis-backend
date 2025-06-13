<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Course;
class RatingCourse extends Model
{
    protected $fillable = [
        'stars',
        'course_id',
        'user_id',
    ];

    /**
     * Relación con el usuario que calificó.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el curso calificado.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
