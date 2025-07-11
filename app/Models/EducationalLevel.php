<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalLevel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'period',
        'max_periods',
    ];
}
