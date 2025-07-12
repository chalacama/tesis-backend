<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Question;
use Illuminate\Database\Eloquent\SoftDeletes;
class TypeQuestion extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nombre',
    ];

    /**
     * Relación: un tipo de pregunta tiene muchas preguntas.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'type_questions_id');
    }
}
