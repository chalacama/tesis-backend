<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\LearningContent;
class CompletedContent extends Model
{
    protected $fillable = [
        'user_id',
        'learning_content_id',
        'completed_at',
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
    public function learningContent()
    {
        return $this->belongsTo(LearningContent::class);
    }
}
