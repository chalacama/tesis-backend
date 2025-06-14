<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeForm extends Model
{
    protected $fillable = [
        'nombre',
        'max_questions',
        'enabled',
    ];

    /**
     * Relación: un tipo tiene muchos formularios.
     */
    public function forms()
    {
        return $this->hasMany(Form::class, 'type_form_id');
    }
}
