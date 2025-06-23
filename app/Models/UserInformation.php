<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
class UserInformation extends Model
{
    protected $fillable = [
        'birthdate',
        'phone_number',
        'province',
        'canton',
        'parish',
        'academic_program',
        'career_id',
        'semester_id',
        'user_id',
    ];

    /**
     * Relación inversa uno a uno con User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: pertenece a una carrera.
     */
    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id');
    }

    /**
     * Relación: pertenece a un semestre.
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    /**
     * Relación: pertenece a un programa académico.
     */
    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class, 'academic_program');
    }
}
