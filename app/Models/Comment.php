<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Course;
use App\Models\ReplyComment;

class Comment extends Model
{
    protected $fillable = [
        'texto',
        'enabled',
        'user_id',
        'curso_id',
    ];

    /**
     * Relación: un comentario pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: un comentario pertenece a un curso.
     */
    public function curso()
    {
        return $this->belongsTo(Course::class, 'curso_id');
    }
    public function replyComments()
    {
        return $this->hasMany(ReplyComment::class, 'comment_id');
    }
}
