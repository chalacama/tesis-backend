<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $fillable = [
        'name',
        'url_logo'
    ];

    /**
     * Relación uno a muchos con UserInformation.
     */
    public function userInformations()
    {
        return $this->hasMany(UserInformation::class, 'career_id');
    }
}
