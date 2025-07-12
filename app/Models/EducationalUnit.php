<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\EducationalLevel;
use App\Models\UnitLevel;
class EducationalUnit extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'organization_domain',
        'url_logo',
    ];
    public function unitLevels()
{
    return $this->hasMany(UnitLevel::class, 'educational_unit_id');
}
}
