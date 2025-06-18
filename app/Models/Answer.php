<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'option',
        'is_correct',
        'question_id',
    ];

    /**
     * Relación: una respuesta pertenece a una pregunta.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    /**
     * Relación uno a muchos con UserAnswer.
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
