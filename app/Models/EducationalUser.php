<?php

namespace App\Models;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class EducationalUser extends Model
{
    protected $fillable = [
        'sede_id',
        'user_id',
        'level',
        'period',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
