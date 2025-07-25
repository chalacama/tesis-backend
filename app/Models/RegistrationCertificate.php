<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Registration;

class RegistrationCertificate extends Model
{
    protected $fillable = [
        'registration_id',
    ];

    /**
     * Relación: un certificado pertenece a una inscripción.
     */
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
