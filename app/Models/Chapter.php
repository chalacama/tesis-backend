<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'modulo_id',
        'learning_content_id',
        'form_id',
        'order',
        'enabled',
    ];

    /**
     * Relación: un capítulo pertenece a un módulo.
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'modulo_id');
    }

    /**
     * Relación: un capítulo tiene un contenido de aprendizaje (uno a uno).
     */
    public function learningContent()
    {
        return $this->belongsTo(LearningContent::class, 'learning_content_id');
    }

    /**
     * Relación: un capítulo puede tener un formulario (uno a uno, pero puede ser compartido).
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    /**
     * Relación: un capítulo tiene muchas preguntas seleccionadas.
     */
    public function chapterQuestions()
    {
        return $this->hasMany(ChapterQuestion::class);
    }
}
