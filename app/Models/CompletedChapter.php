<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Chapter;
class CompletedChapter extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
        'content_at',
        'test_at',
    ];

    /**
     * Relación: un registro pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: un registro pertenece a un contenido de aprendizaje.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
