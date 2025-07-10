<?php

namespace App\Http\Requests;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CreateTutorCourseRequest extends FormRequest
{
   public function authorize(): bool
    {
        return true; // ajusta según tus políticas
    }

    public function rules(): array
    {
        return [
            'enabled'   => ['required', 'boolean'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'user_id'   => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'enabled.required'   => 'El campo enabled es obligatorio',
            'enabled.boolean'    => 'El campo enabled debe ser verdadero o falso',
            'course_id.required' => 'El ID del curso es obligatorio',
            'course_id.integer'  => 'El ID del curso debe ser un número entero',
            'course_id.exists'   => 'El curso especificado no existe',
            'user_id.required'   => 'El ID del usuario es obligatorio',
            'user_id.integer'    => 'El ID del usuario debe ser un número entero',
            'user_id.exists'     => 'El usuario especificado no existe',
        ];
    }
}
