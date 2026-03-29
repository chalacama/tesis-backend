<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Course;
class MiniatureCourse extends Model
{
    
    protected $fillable = [
        'course_id',
        'url',
        'name',
        'size_bytes',
        'width',
        'height',
        'type_thumbnail_id'
    ];

    /**
     * Relación: una miniatura pertenece a un curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    /**
     * Relación: una miniatura pertenece a un tipo de miniatura.
     */
    public function typeThumbnail()
    {
        return $this->belongsTo(TypeThumbnail::class);
    }
}
