<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateLearningContentRequest;
use App\Models\LearningContent;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\TypeLearningContent;
use App\Models\Chapter;
use Exception;
// use Cloudinary\Cloudinary;
use Cloudinary\Api\Admin\AdminApi;
class LearningContentController extends Controller
{
    /**
     * Sube un archivo a Cloudinary y crea un nuevo registro de LearningContent.
     *
     * @param CreateLearningContentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLearningContent(CreateLearningContentRequest $request)
    {
        try {
            // 3. Subir el archivo a Cloudinary
            // El 'resource_type' => 'auto' permite que Cloudinary detecte si es video, imagen, etc.
            $uploadedFile = Cloudinary::upload($request->file('file')->getRealPath(), [
                'folder' => 'learning_content', // Carpeta opcional en Cloudinary
                'resource_type' => 'auto',
            ]);

            // 4. Obtener la URL segura del archivo subido
            $secureUrl = $uploadedFile->getSecurePath();

            // 5. Crear el registro en la base de datos
            $learningContent = LearningContent::create([
                'url' => $secureUrl,
                'type_content_id' => $request->type_content_id,
                'chapter_id' => $request->chapter_id,
                'enabled' => true, // Por defecto, el contenido está habilitado
            ]);

            // 6. Devolver una respuesta exitosa
            return response()->json([
                'message' => 'Contenido de aprendizaje creado exitosamente.',
                'data' => $learningContent
            ], 201); // 201 Created

        } catch (Exception $e) {
            // Registrar el error para depuración
            Log::error('Error al subir contenido a Cloudinary: ' . $e->getMessage());

            // Devolver una respuesta de error genérica
            return response()->json([
                'message' => 'Ocurrió un error en el servidor al procesar tu solicitud.'
            ], 500); // 500 Internal Server Error
        }
    }
}
