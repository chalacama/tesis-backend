<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
class TestViewAnswer extends Model implements Sortable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use SortableTrait;
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
