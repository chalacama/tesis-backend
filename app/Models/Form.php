<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TypeForm;
use App\Models\Question;
class Form extends Model
{
    protected $fillable = [
        'title',
        'order',
        'random_questions',
        'enabled',
        'type_form_id',
    ];

    /**
     * Relación: un formulario pertenece a un tipo de formulario.
     */
    public function typeForm()
    {
        return $this->belongsTo(TypeForm::class, 'type_form_id');
    }
    /**
     * Relación: un formulario tiene muchas preguntas.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'form_id');
    }
    public function chapters()
    {
    return $this->hasMany(Chapter::class, 'form_id');
    }
}
