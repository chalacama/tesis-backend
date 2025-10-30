<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
class TestViewAnswer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use Sortable;
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];
    protected $fillable = [
        'test_view_question_id',
        'answer_id',
        'order',
    ];

    /**
     * Obtiene la pregunta ordenada (de test_view_questions) a la que pertenece.
     */
    public function testViewQuestion(): BelongsTo
    {
        return $this->belongsTo(TestViewQuestion::class);
    }

    /**
     * Obtiene la información de la respuesta original (texto de la opción, is_correct).
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }
}
