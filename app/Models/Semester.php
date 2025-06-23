<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Relación uno a muchos con UserInformation.
     */
    public function userInformations()
    {
        return $this->hasMany(UserInformation::class, 'semester_id');
    }
}
