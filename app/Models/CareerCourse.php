<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Course;
use App\Models\Career;
use Illuminate\Database\Eloquent\Model;

class CareerCourse extends Model
{
    protected $fillable = [
        'course_id',
        'career_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function career()
    {
        return $this->belongsTo(Career::class);
    }
    
}
