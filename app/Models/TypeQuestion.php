<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Question;

class TypeQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
    ];

    /**
     * Un tipo de pregunta tiene muchas preguntas.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'type_questions_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
