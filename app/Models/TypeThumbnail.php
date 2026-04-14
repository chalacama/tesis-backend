<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class TypeThumbnail extends Model
{
    use HasFactory ;
    protected $fillable = [
        'name',
        'max_size_bytes',
        'width',
        'height',
        'aspect_ratio',
        'enabled'
    ];

    /**
     * Relación: un tipo de miniatura tiene muchas miniaturas de curso.
     */
    public function miniatureCourses()
    {
        return $this->hasMany(MiniatureCourse::class);
    }
}
