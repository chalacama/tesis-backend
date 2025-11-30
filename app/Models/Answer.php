<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Question;
use App\Models\UserAnswer;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
// use Spatie\EloquentSortable\Sortable;
class Answer extends Model implements Sortable
{
    use SortableTrait ;
    
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];
    protected $fillable = [
        'option',
        'is_correct',
        'order',
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
