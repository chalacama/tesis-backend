<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
class UserInformation extends Model
{
    protected $fillable = [
        'birthdate',
        'province_id',
        'canton_id',
        'parish_id',
        'user_id',
        'sexo',
        'estado_civil',
        'discapacidad',
        'discapacidad_permanente',
        'asistencia_establecimiento_discapacidad',
    ];
    protected $casts = [
        'birthdate' => 'date',
    ];
    /**
     * Relación inversa uno a uno con User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }



}
