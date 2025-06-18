<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LearningContent;
class TypeLearningContent extends Model
{
    protected $fillable = [
        'name',
        'max_size',
        'max_duration_seconds',
        'enabled',
    ];

    /**
     * Relación: un tipo tiene muchos contenidos de aprendizaje.
     */
    public function learningContents()
    {
        return $this->hasMany(LearningContent::class, 'type_content_id');
    }
}
