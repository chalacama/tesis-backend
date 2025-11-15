<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\EducationalUnit;
use App\Models\CareerSede;
use Illuminate\Database\Eloquent\SoftDeletes;
class Sede extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'province',
        'canton',
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
}
