<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\ModuleAttempt;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Module extends Model implements Sortable
{
    use SoftDeletes,SortableTrait; 
    
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'name',
        'order',
        'enabled',
        'course_id',
    ];

    // Este método sigue siendo el mismo y es correcto
    public function buildSortQuery()
    {
        return static::query()->where('course_id', $this->course_id);
    }
    /**
     * Relación: un módulo pertenece a un curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
     /**
     * Relación: un módulo tiene muchos capítulos.
     */
    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'module_id');
    }
    /**
     * Relación uno a muchos con ModuleAttempt.
     */
    public function moduleAttempts()
    {
        return $this->hasMany(ModuleAttempt::class);
    }
}
