<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Chapter;
class LikeChapter extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
    ];

    /**
     * Relación: un like pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: un like pertenece a un contenido de aprendizaje.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}
