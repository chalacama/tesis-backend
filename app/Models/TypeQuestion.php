<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Question;
class TypeQuestion extends Model
{
    protected $fillable = [
        'nombre',
        'enabled',
    ];

    /**
     * Relación: un tipo de pregunta tiene muchas preguntas.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'type_questions_id');
    }
}
