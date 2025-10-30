<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
class TestViewQuestion extends Model
{
    use Sortable;
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'test_view_id',
        'question_id',
        'order',
    ];

    /**
     * Obtiene el intento de test (test_view) al que pertenece esta pregunta ordenada.
     */
    public function testView(): BelongsTo
    {
        return $this->belongsTo(TestView::class);
    }

    /**
     * Obtiene la información de la pregunta original (enunciado, etc.).
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Obtiene la lista de respuestas ORDENADAS para esta pregunta en este intento.
     * * Uso: $testViewQuestion->testViewAnswers
     */
    public function testViewAnswers(): HasMany
    {
        // Importante: Las devolvemos ya ordenadas según se guardaron.
        return $this->hasMany(TestViewAnswer::class)->orderBy('order');
    }
}
