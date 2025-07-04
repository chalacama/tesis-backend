<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- AÑADIR ESTA LÍNEA
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'texto',
        'parent_id',
        'commentable_id',
        'commentable_type',
        'enabled',
    ];

    /**
     * Relación: Un comentario pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación Polimórfica: Obtiene el modelo padre del comentario (Course, Chapter, etc.).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relación: Un comentario puede tener muchas respuestas (que también son comentarios).
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Relación: Una respuesta pertenece a un comentario padre.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Relación: un comentario pertenece a un curso.
     */
    /* public function curso()
    {
        return $this->belongsTo(Course::class, 'curso_id');
    } */
    
}
