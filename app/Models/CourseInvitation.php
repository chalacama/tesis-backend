<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseInvitation extends Model
{
    protected $fillable = [
        'course_id',
        'inviter_id',
        'email',
        'token',
        'status',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
