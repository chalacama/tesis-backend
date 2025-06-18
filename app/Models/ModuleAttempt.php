<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Module;
class ModuleAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'module_id',
        'attempts_count',
        'last_attempt_at',
        'approved',
    ];

    /**
     * Relación: un intento pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: un intento pertenece a un módulo.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
