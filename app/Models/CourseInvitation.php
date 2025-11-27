<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseInvitation extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'email',
        'token',
        'status',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function inviter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
