<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Course;
class SavedCourse extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
    ];

    /**
     * Relación: un guardado pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: un guardado pertenece a un curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
