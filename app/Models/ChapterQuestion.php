<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterQuestion extends Model
{
    protected $fillable = [
        'order',
        'chapter_id',
        'question_id',
    ];

    /**
     * Relación: pertenece a un capítulo.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Relación: pertenece a una pregunta.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
