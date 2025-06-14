<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningContent extends Model
{
    protected $fillable = [
        'name',
        'description',
        'url',
        'iframe',
        'enabled',
        'type_content_id',
    ];

    /**
     * Relación: un contenido pertenece a un tipo de contenido.
     */
    public function typeLearningContent()
    {
        return $this->belongsTo(TypeLearningContent::class, 'type_content_id');
    }
    public function chapter()
{
    return $this->hasOne(Chapter::class, 'learning_content_id');
}
}
