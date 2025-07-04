<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Module;
use App\Models\LearningContent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
class Chapter extends Model implements Sortable
{
    use SoftDeletes,SortableTrait; 
    
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];
    protected $fillable = [
        'title',
        'description',
        'order',
        'enabled',
        'module_id',
    ];
    public function buildSortQuery()
    {
        return static::query()->where('module_id', $this->module_id);
    }
    /**
     * Relación: un capítulo pertenece a un módulo.
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
    /**
     * Relación: un capítulo tiene un contenido de aprendizaje (uno a uno).
     */
    public function learningContent()
    {
        return $this->hasOne(LearningContent::class, 'chapter_id');
    }

    /**
     * Relación: un capítulo tiene muchas preguntas.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'chapter_id');
    }

}
