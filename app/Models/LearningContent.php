<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TypeLearningContent;
use App\Models\Chapter;
use App\Models\ContentView;


class LearningContent extends Model
{
    
    protected $fillable = [
        'url',
        'size_bytes',
        'duration_seconds',
        'type_content_id',
        'chapter_id',
        'format_id'
    ];

    /**
     * Relación: un contenido pertenece a un tipo de contenido.
     */
    public function typeLearningContent()
    {
        return $this->belongsTo(TypeLearningContent::class, 'type_content_id');
    }
    /**
     * Relación: un contenido pertenece a un capítulo (uno a uno inverso).
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
    
    
    /**
     * Relación uno a muchos con ContentView.
     */
    public function contentViews()
    {
        return $this->hasMany(ContentView::class);
    }
    
    /**
     * Relación: un contenido tiene un formato específico.
     */
    public function format()
    {
        return $this->belongsTo(Format::class, 'format_id');
    }

}
