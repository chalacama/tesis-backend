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
    public function careerSedes()
    {
    return $this->hasMany(CareerSede::class);
    }
}
