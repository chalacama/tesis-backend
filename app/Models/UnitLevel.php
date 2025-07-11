<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\EducationalUnit;
use App\Models\EducationalLevel;
class UnitLevel extends Model
{
    protected $fillable = [
        'educational_unit_id',
        'educational_level_id',
    ];

    public function educationalUnit()
    {
        return $this->belongsTo(EducationalUnit::class);
    }

    public function educationalLevel()
    {
        return $this->belongsTo(EducationalLevel::class);
    }
}
