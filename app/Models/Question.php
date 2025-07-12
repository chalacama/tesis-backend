<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TypeQuestion;
use App\Models\Answer;
use App\Models\UserAnswer;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\SoftDeletes;
class Question extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'statement',
        'spot',
        'type_questions_id',
        'chapter_id',
    ];

    /**
     * Relación: una pregunta pertenece a un tipo de pregunta.
     */
    public function typeQuestion()
    {
        return $this->belongsTo(TypeQuestion::class, 'type_questions_id');
    }
    /**
     * Relación: una pregunta pertenece a un capítulo.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    /**
     * Relación: una pregunta tiene muchas respuestas.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
    /**
     * Relación uno a muchos con UserAnswer.
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
