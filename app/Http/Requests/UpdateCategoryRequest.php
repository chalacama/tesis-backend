<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class UpdateCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $category = $this->route('category');
        $categoryId = $category instanceof \App\Models\Category ? $category->id : $category;

        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $categoryId,
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $validator->errors()
            ], 422)
        );
    }
    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string'   => 'El nombre debe ser una cadena de texto válida.',
            'name.max'      => 'El nombre no puede tener más de 255 caracteres.',
            'name.unique'   => 'Ya existe otra categoría con este nombre.',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombre de la categoría',
        ];
    }
}
