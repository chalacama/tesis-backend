<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    protected $fillable = ['texto', 'searched', 'user_id', 'search_type', 'entity_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
