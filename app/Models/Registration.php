<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Course;
class Registration extends Model
{
    protected $fillable = [
        'approved',
        'annulment',
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
    public function certificate()
    {
        return $this->hasOne(RegistrationCertificate::class);
    }
}
