<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\LearningContent;
use App\Models\TypeLearningContent;
use Cloudinary\Api\Admin\AdminApi;
use Illuminate\Validation\ValidationException;
use Cloudinary\Cloudinary;
use Exception;

class LearningContentController extends Controller
{
    
    public function createVideoCloudinary(Request $request)
    {
        // 1. Validación de entrada
        $validated = $request->validate([
            'file'             => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo',
            'chapter_id'       => 'required|integer|exists:chapters,id',
            'type_content_id'  => 'required|integer|exists:type_learning_contents,id',
            'duration_seconds' => 'required|integer|min:1',
        ]);

        // 2. Validar tipo de contenido
        $type = TypeLearningContent::findOrFail($validated['type_content_id']);

        if ($type->name !== 'cloudinary' || !$type->enabled) {
            throw ValidationException::withMessages([
                'type_content_id' => ['El tipo de contenido debe ser "cloudinary" y estar habilitado.']
            ]);
        }

        // 3. Validar tamaño del archivo
        $maxSizeKB = ((float) $type->max_size_mb) * 1024;
        if (($request->file('file')->getSize() / 1024) > $maxSizeKB) {
            throw ValidationException::withMessages([
                'file' => ["El video supera el tamaño máximo permitido de {$type->max_size_mb}MB."]
            ]);
        }

        // 4. Validar duración
        $duration = $validated['duration_seconds'];
        if ($duration < $type->min_duration_seconds || $duration > $type->max_duration_seconds) {
            throw ValidationException::withMessages([
                'duration_seconds' => [
                    "La duración debe estar entre {$type->min_duration_seconds} y {$type->max_duration_seconds} segundos."
                ]
            ]);
        }

        // 5. Subir video a Cloudinary
        try {
            $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));

            $upload = $cloudinary->uploadApi()->upload(
                $request->file('file')->getRealPath(),
                [
                    'resource_type' => 'video',
                    'folder'        => 'learning_content/videos',
                ]
            );
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir a Cloudinary: ' . $e->getMessage()
            ], 500);
        }

        // 6. Crear registro en la base de datos
        $content = LearningContent::create([
            'url'             => $upload['secure_url'],
            'enabled'         => true,
            'type_content_id' => $type->id,
            'chapter_id'      => $validated['chapter_id'],
        ]);

        // 7. Retornar éxito
        return response()->json([
            'success' => true,
            'data'    => $content
        ], 201);
    }
   
public function destroyVideoCloudinary($id)
    {
        // 1. Encuentra el registro en la BD o falla con un error 404
        $content = LearningContent::findOrFail($id);

        try {
            // 2. Extraer el 'public_id' de la URL del video.
            // Esta es la parte más importante. Cloudinary necesita este ID.
            // Asumimos que la carpeta de subida fue 'learning_content/videos'.
            $filename = pathinfo($content->url, PATHINFO_FILENAME);
            $publicId = 'learning_content/videos/' . $filename;

            // 3. Instanciar el cliente de Cloudinary
            $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));

            // 4. Llamar a la API para destruir el video
            // Es CRUCIAL especificar 'resource_type' => 'video'
            $cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => 'video',
                'invalidate' => true //ELIMINAR PERMANTEMENTE
            ]);

            // 5. Si todo fue bien con Cloudinary, forzar el borrado en la BD
            // forceDelete() ignora los SoftDeletes
            $content->forceDelete();

        } catch (Exception $e) {
            // Si algo falla (ej: el video ya no existe en Cloudinary), devuelve un error.
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el contenido: ' . $e->getMessage()
            ], 500);
        }

        // 6. Responder con éxito
        return response()->json([
            'success' => true,
            'message' => 'Video y registro eliminados permanentemente.'
        ]);
    }
    public function softDeleteCourse($id)
    {
        $learningContent = LearningContent::find($id);

        if (!$learningContent) {
            return response()->json(['message' => 'Contenido no encontrado'], 404);
        }

        $learningContent->delete();

        return response()->json([
            'message' => 'Contenido enviado a papelería correctamente'
        ]);
    }

}
