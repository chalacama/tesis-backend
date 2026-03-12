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
        'enabled',
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

    /**
     * Un tipo tiene muchos formatos (Ej: archive tiene pdf, mp4, etc).
     */
    public function formats()
    {
        return $this->hasMany(Format::class, 'type_learning_content_id');
    }
}
