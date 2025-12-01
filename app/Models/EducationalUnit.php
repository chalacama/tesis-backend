<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EducationalLevel;
use App\Models\UnitLevel;
class EducationalUnit extends Model
{
    
    protected $fillable = [
        'name',
        'organization_domain',
        'url_logo',
    ];
    public function unitLevels()
    {
        return $this->hasMany(UnitLevel::class, 'educational_unit_id');
    }

    // ➕ NUEVO: para obtener directamente los niveles educativos
    public function educationalLevels()
    {
        return $this->belongsToMany(EducationalLevel::class, 'unit_levels', 'educational_unit_id', 'educational_level_id');
    }

    /**
     * Sedes que pertenecen a esta unidad educativa.
     */
    public function sedes()
    {
        return $this->hasMany(Sede::class);
    }

    /**
     * Usuarios educativos asociados a la unidad a través de las sedes.
     */
    public function educationalUsers()
    {
        return $this->hasManyThrough(
            EducationalUser::class, // related
            Sede::class,            // through
            'educational_unit_id',  // FK en sedes que apunta a educational_units.id
            'sede_id',              // FK en educational_users que apunta a sedes.id
            'id',                   // local key en educational_units
            'id'                    // local key en sedes
        );
    }

}
