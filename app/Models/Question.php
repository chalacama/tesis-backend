<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TypeQuestion;
use App\Models\Form;
class Question extends Model
{
    protected $fillable = [
        'statement',
        'order',
        'enabled',
        'type_questions_id',
        'form_id',
    ];

    /**
     * Relación: una pregunta pertenece a un tipo de pregunta.
     */
    public function typeQuestion()
    {
        return $this->belongsTo(TypeQuestion::class, 'type_questions_id');
    }

    /**
     * Relación: una pregunta pertenece a un formulario.
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }
}
