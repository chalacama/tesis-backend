<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TypeQuestion;
use App\Models\Answer;
use App\Models\UserAnswer;
use App\Models\Chapter;
use App\Models\Test;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;


class Question extends Model implements Sortable
{
    use SortableTrait;
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];
    protected $fillable = [
        'statement',
        'spot',
        'order',
        'type_questions_id',
        'test_id',
        
    ];

    /**
     * Relación: una pregunta pertenece a un tipo de pregunta.
     */
    public function typeQuestion()
    {
        return $this->belongsTo(TypeQuestion::class, 'type_questions_id');
    }
    
    /**
     * Relación: una pregunta pertenece a un test.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
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
