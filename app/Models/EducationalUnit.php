<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalUnit extends Model
{
    protected $fillable = [
        'name',
        'organization_domain',
        'url_logo',
    ];
}
