<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\LearningContent;
class LikeLearningContent extends Model
{
    protected $fillable = [
        'user_id',
        'learning_contents_id',
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
    public function learningContent()
    {
        return $this->belongsTo(LearningContent::class, 'learning_contents_id');
    }
}
