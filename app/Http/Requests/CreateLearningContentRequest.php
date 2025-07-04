<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\TypeLearningContent;
use App\Models\Chapter;
class CreateLearningContentRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        // Cambia a true para permitir la solicitud.
        // Puedes agregar lógica de autorización más compleja aquí si es necesario.
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chapter_id' => [
                'required',
                'integer',
                Rule::exists('chapters', 'id'),
                Rule::unique('learning_contents', 'chapter_id'),
            ],
            'type_content_id' => [
                'required',
                'integer',
                // Esta regla es más robusta. Verifica dos cosas a la vez:
                // 1. Que el ID exista en la tabla 'type_learning_contents'.
                // 2. Que el registro encontrado tenga el 'name' igual a 'cloudinary'.
                // Si no se cumple, devolverá un error de validación 422, no un error 500.
                Rule::exists('type_learning_contents', 'id')->where(function ($query) {
                    return $query->where('name', 'cloudinary');
                }),
            ],
            'file' => [
                'required',
                'file',
                'mimetypes:video/x-msvideo,video/mp4,video/mpeg,video/ogg,video/webm,video/x-flv,video/quicktime',
                'max:512000', // 500MB
            ],
        ];
    }
    
    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'chapter_id.unique' => 'Este capítulo ya tiene un contenido de aprendizaje asociado.',
            // Mensaje de error más claro para el usuario si envía un tipo incorrecto.
            'type_content_id.exists' => 'El tipo de contenido seleccionado no es válido o no corresponde a una subida de Cloudinary.',
            'file.required' => 'Es obligatorio adjuntar un archivo.',
            'file.mimetypes' => 'El archivo debe ser un video en un formato válido.',
            'file.max' => 'El archivo no debe superar los 500MB.',
        ];
    }
}
