<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\EducationalUnit;
class Sede extends Model
{
    protected $fillable = [
        'province',
        'canton',
        'educational_unit_id',
    ];

    public function educationalUnit()
    {
        return $this->belongsTo(EducationalUnit::class);
    }
}
