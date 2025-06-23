<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCategoryInterest extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
    ];

    /**
     * Relación: este interés pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: este interés pertenece a una categoría.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
