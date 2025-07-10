<?php

namespace App\Http\Requests;

use App\Models\Chapter;
use App\Models\LearningContent;
use App\Models\TypeLearningContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
class CreateVideoCloudinaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Cambia si deseas proteger con políticas
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo',
            'chapter_id' => 'required|integer|exists:chapters,id',
            'type_content_id' => 'required|integer|exists:type_learning_contents,id',
            'duration_seconds' => 'required|integer|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
        $chapter_id = request('chapter_id');
        $type_content_id = request('type_content_id');
        $duration = request('duration_seconds');

            
           // Validar si el capítulo ya tiene contenido
    $chapter = Chapter::find($chapter_id);
        if ($chapter->learningContent()->exists()) {
        $validator->errors()->add('chapter_id', 'Este capítulo ya tiene contenido asignado.');
    }

            // Validar el tipo de contenido y reglas dinámicas
            if ($type_content_id) {
                $type = TypeLearningContent::find($type_content_id);
                if (!$type) {
                    $validator->errors()->add('type_content_id', 'Tipo de contenido no encontrado.');
                    return;
                }

                if ($type->name !== 'cloudinary' || !$type->enabled) {
                    $validator->errors()->add('type_content_id', 'El tipo de contenido debe ser "cloudinary" y estar habilitado.');
                }

                // Validar tamaño del archivo
                if ($type->max_size_mb && request()->hasFile('file')) {
                    $maxSizeKB = ((float) $type->max_size_mb) * 1024;
                    if ((request()->file('file')->getSize() / 1024) > $maxSizeKB) {
                        $validator->errors()->add('file', "El video supera el tamaño máximo permitido de {$type->max_size_mb}MB.");
                    }
                }

                // Validar duración
                if ($duration) {
                    if ($duration < $type->min_duration_seconds || $duration > $type->max_duration_seconds) {
                        $validator->errors()->add('duration_seconds', 
                            "La duración debe estar entre {$type->min_duration_seconds} y {$type->max_duration_seconds} segundos."
                        );
                    }
                }
            }
        });
    }
}
