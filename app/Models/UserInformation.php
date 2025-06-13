<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
class UserInformation extends Model
{
    //
    /**
     * Relación inversa uno a uno con User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
