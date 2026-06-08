<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
class VerificationCode extends Model
{
    //
    protected $fillable = [
        'user_id',
        'code',       // Se guarda encriptado (Hash::make)
        'type',       // 'email_verification', 'phone_verification', 'password_reset'
        'channel',    // 'email', 'whatsapp'
        'expires_at', // Fecha de expiración
        'used_at',    // Para marcar uso
    ];

    // Castings (mejor manejo de fechas)
    protected $casts = [
        'expires_at' => 'datetime', 
        'used_at' => 'datetime',
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // SCOPES para buscar códigos eficientemente
    // Busca un código activo para un usuario y tipo específico
    public function scopeActive($query, $userId, $type, $channel = null)
    {
        return $query->where('user_id', $userId)
            ->where('type', $type)
            ->where('used_at', null) // No usado
            ->where('expires_at', '>', now()) // No expirado
            ->when($channel, function ($q) use ($channel) {
                return $q->where('channel', $channel);
            });
    }

    // Método para marcar un código como usado
    public function markAsUsed()
    {
        $this->update(['used_at' => now()]);
    }

    // Método para generar código unico
    public static function generateCode($length = 6)
    {
        // Generate 6 random digits unique (000000 - 999999)
        do {
            $code = str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        } while (self::where('code', Hash::make($code))->exists());
        
        return $code;
    }
        public static function generateVerifyCode($length = 6)
    {
        // Generate 6 random digits unique (000000 - 999999)
        do {
            $code = str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        } while (self::where('code', Hash::make($code))->exists());
        
        return $code;
    }
    
}
