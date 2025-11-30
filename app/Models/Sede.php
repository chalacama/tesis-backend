<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\EducationalUnit;
use App\Models\Career;
use App\Models\EducationalUser;

class Sede extends Model
{
    use HasFactory;

    protected $fillable = [
        'contry',
        'province_id',
        'canton_id',
        'educational_unit_id',
    ];

    public function educationalUnit()
    {
        return $this->belongsTo(EducationalUnit::class);
    }

    // Carreras asociadas a la sede (tabla pivote career_sedes)
    public function careers()
    {
        return $this->belongsToMany(Career::class, 'career_sedes', 'sede_id', 'career_id');
    }

    // Usuarios educativos asociados a la sede (para conteo en indexAll)
    public function educationalUsers()
    {
        return $this->hasMany(EducationalUser::class);
    }
}
