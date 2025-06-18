<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $fillable = [
        'answer_id',
        'user_id',
        'question_id',
        'is_correct',
        'answered_at',
    ];

    /**
     * Relación: una respuesta de usuario pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: una respuesta de usuario pertenece a una respuesta.
     */
    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    /**
     * Relación: una respuesta de usuario pertenece a una pregunta.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
