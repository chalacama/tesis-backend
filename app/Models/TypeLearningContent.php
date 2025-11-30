<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\LearningContent;

class TypeLearningContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'max_size_mb',
        'min_duration_seconds',
        'max_duration_seconds',
    ];

    /**
     * Un tipo tiene muchos contenidos de aprendizaje.
     */
    public function learningContents()
    {
        return $this->hasMany(LearningContent::class, 'type_content_id');
    }

    /**
     * Scope para obtener solo tipos activos (no archivados).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
