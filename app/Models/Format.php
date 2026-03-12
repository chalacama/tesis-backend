<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Format extends Model
{
    use SoftDeletes; // No olvides agregarlo porque lo pusiste en tu migración

    protected $fillable = [
        'name',
        'max_size_mb',
        'min_duration_seconds',
        'max_duration_seconds',
        'enabled',
        'type_learning_content_id'
    ];

    /**
     * Relación: Un formato pertenece a un tipo de contenido.
     */
    public function typeLearningContent()
    {
        return $this->belongsTo(TypeLearningContent::class, 'type_learning_content_id');
    }
    
    /**
     * Relación: Un formato puede ser usado en muchos contenidos de aprendizaje.
     */
    public function learningContents()
    {
        return $this->hasMany(LearningContent::class, 'format_id');
    }
}