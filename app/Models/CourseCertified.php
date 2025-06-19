<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseCertified extends Model
{
    protected $fillable = [
        'course_id',
        'is_certified',
        'is_unlimited',
        'max_attempts',
    ];

    /**
     * Relación: un certificado pertenece a un curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
