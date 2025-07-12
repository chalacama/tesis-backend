<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\SoftDeletes;
class TutorCourse extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'course_id',
        'user_id',
    ];

    /**
     * Relación con el usuario tutor.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
