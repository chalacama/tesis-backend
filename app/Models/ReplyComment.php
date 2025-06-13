<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Comment;
class ReplyComment extends Model
{
    protected $fillable = [
        'texto',
        'user_id',
        'comment_id',
    ];

    /**
     * Relación: una respuesta pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: una respuesta pertenece a un comentario.
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }
}
