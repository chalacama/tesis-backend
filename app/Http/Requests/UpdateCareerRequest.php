<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCareerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:careers,name,' . $this->career->id,
            'max_semesters' => 'required|integer|min:1',
            'url_logo' => 'required|url|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre de la carrera es obligatorio.',
            'name.string' => 'El nombre de la carrera debe ser una cadena de texto.',
            'name.max' => 'El nombre de la carrera no debe superar los 255 caracteres.',
            'name.unique' => 'Ya existe una carrera con ese nombre.',

            'max_semesters.required' => 'El número máximo de semestres es obligatorio.',
            'max_semesters.integer' => 'El número máximo de semestres debe ser un número entero.',
            'max_semesters.min' => 'El número máximo de semestres debe ser al menos 1.',

            'url_logo.required' => 'La URL del logo es obligatoria.',
            'url_logo.url' => 'Debe proporcionar una URL válida para el logo.',
            'url_logo.max' => 'La URL del logo no debe superar los 255 caracteres.',
        ];
    }
}
