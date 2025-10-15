<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TypeLearningContent;
use App\Models\Chapter;
use App\Models\ContentView;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningContent extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'url',
        'type_content_id',
        'chapter_id'
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
    

}
