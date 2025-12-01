<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EducationalUser;
use App\Models\EducationalUnit;

class EducationalLevel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'period',
        'max_periods',
    ];

    /**
     * Usuarios educativos que están en este nivel.
     * FK: educational_users.educational_level_id
     */
    public function educationalUsers()
    {
        return $this->hasMany(EducationalUser::class);
    }

    /**
     * Unidades educativas que manejan este nivel.
     * Pivot: unit_levels (educational_unit_id, educational_level_id)
     */
    public function educationalUnits()
    {
        return $this->belongsToMany(
            EducationalUnit::class,
            'unit_levels',
            'educational_level_id',
            'educational_unit_id'
        );
    }
}

