<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LearningContent;
use Illuminate\Database\Eloquent\SoftDeletes;
class TypeLearningContent extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'max_size_mb',
        'min_duration_seconds',
        'max_duration_seconds',
    ];

    /**
     * Relación: un tipo tiene muchos contenidos de aprendizaje.
     */
    public function learningContents()
    {
        return $this->hasMany(LearningContent::class, 'type_content_id');
    }
}
