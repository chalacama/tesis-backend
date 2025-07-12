<?php

namespace App\Models;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\EducationalLevel;

class EducationalUser extends Model
{
    protected $fillable = [
        'sede_id',
        'user_id',    
        'career_id',    
        'educational_level_id',
        'level',
        
    ];
public static function boot()
{
    parent::boot();

    static::saving(function ($model) {
        // Validar que la unidad educativa tenga ese tipo de educación
        if ($model->sede && $model->educational_level_id) {
            $hasLevel = $model->sede->educationalUnit
                && $model->sede->educationalUnit->unitLevels()
                    ->where('educational_level_id', $model->educational_level_id)
                    ->exists();

            if (!$hasLevel) {
                $model->educational_level_id = null;
            } else {
                // Validar que el nivel no exceda el máximo permitido
                $level = EducationalLevel::find($model->educational_level_id);
                if ($level && $model->level > $level->max_periods) {
                    $model->level = $level->max_periods;
                }
            }
        }
        // Validación de carrera como antes...
        if ($model->sede && $model->career_id) {
            $exists = $model->sede->careerSedes()->where('career_id', $model->career_id)->exists();
            if (!$exists) {
                $model->career_id = null;
            }
        }
    });
}
    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function educationalLevel()
{
    return $this->belongsTo(EducationalLevel::class);
}
/* public function unitLevels()
{
    return $this->hasMany(\App\Models\UnitLevel::class, 'educational_unit_id');
} */
}
