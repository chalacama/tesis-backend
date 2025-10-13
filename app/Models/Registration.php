<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Course;
use App\Models\Certificate;
class Registration extends Model
{
    protected $fillable = [       
       
        'user_id',
        'course_id',
    ];

    /**
     * Relación: una inscripción pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: una inscripción pertenece a un curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    /**
     * Relación uno a uno con RegistrationCertificate.
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
