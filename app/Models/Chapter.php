<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Module;
use App\Models\LearningContent;
class Chapter extends Model
{
    protected $fillable = [
        'name',
        'description',
        'order',
        'enabled',
        'module_id',
    ];

    /**
     * Relación: un capítulo pertenece a un módulo.
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
    /**
     * Relación: un capítulo tiene un contenido de aprendizaje (uno a uno).
     */
    public function learningContent()
    {
        return $this->hasOne(LearningContent::class, 'chapter_id');
    }

    /**
     * Relación: un capítulo tiene muchas preguntas.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'chapter_id');
    }

}
