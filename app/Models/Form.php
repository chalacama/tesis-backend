<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = [
        'title',
        'order',
        'random_questions',
        'enabled',
        'type_form_id',
    ];

    /**
     * Relación: un formulario pertenece a un tipo de formulario.
     */
    public function typeForm()
    {
        return $this->belongsTo(TypeForm::class, 'type_form_id');
    }
}
