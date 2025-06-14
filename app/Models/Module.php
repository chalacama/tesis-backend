<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
class Module extends Model
{
    protected $fillable = [
        'name',
        'order',
        'enabled',
        'course_id',
    ];

    /**
     * Relación: un módulo pertenece a un curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
     /**
     * Relación: un módulo tiene muchos capítulos.
     */
    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'modulo_id');
    }
}
