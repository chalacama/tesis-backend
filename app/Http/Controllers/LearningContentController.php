<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateVideoCloudinaryRequest;
use App\Models\Chapter;
use Illuminate\Http\Request;
use App\Models\LearningContent;
use App\Models\TypeLearningContent;
use Cloudinary\Api\Admin\AdminApi;
use Illuminate\Validation\ValidationException;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use Exception;

class LearningContentController extends Controller
{    
    
// Método para obtener URL firmada del video
public function getSecureVideoUrl($publicId) 
{
    $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
    
    $url = $cloudinary->video($publicId)
        ->delivery('authenticated')
        ->signUrl(true) // ¡Clave para la firma!
        ->toUrl();
    
    return $url;
    // Al generar la URL de entrega
    // $url = $cloudinary->tag('video')
    //     ->publicId('learning_content/videos/tu_video')
    //     ->delivery('authenticated')
    //     ->signUrl(true) // ¡Esto es clave!
    //     ->toUrl();
}
public function storeCloud(CreateVideoCloudinaryRequest $request)
    {
        set_time_limit(300);

        try {
            $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
            $upload = $cloudinary->uploadApi()->upload(
                $request->validated()['file']->getRealPath(),
                [
                    'resource_type' => 'video',
                    'folder' => 'learning_content/videos',
                    /* 'type' => 'authenticated', */
                    'chunk_size' => 6000000 // 6MB chunks
                ]
            );
            // Generar URL firmada después de la subida
        /* $signedUrl = $cloudinary->video($upload['public_id'])
            ->delivery('authenticated')
            ->signUrl(true) // ¡Clave para la firma!
            ->toUrl(); */
        } catch (Exception $e) {
            Log::error("Error al subir a Cloudinary: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir a Cloudinary. Intenta nuevamente.'
            ], 500);
        }

        $content = LearningContent::create([
            'url' => $upload['secure_url'],
            'enabled' => true,
            'type_content_id' => $request->validated()['type_content_id'],
            'chapter_id' => $request->validated()['chapter_id'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $content,//$signedUrl
        ], 201);
    }
public function destroyCloud($id)
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
public function archived($id)
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
public function activate(Request $request, $id)
{
    $validated = $request->validate([
        'activate' => 'required|boolean',
    ]);

    $learningContent = LearningContent::find($id);

    if (!$learningContent) {
        return response()->json(['message' => 'Contenido de aprendizaje no encontrado'], 404);
    }

    if ($validated['activate'] && $learningContent->enabled) {
        return response()->json([
            'message' => 'El contenido de aprendizaje ya está activado',
            'learning_content' => $learningContent
        ]);
    }

    if (!$validated['activate'] && !$learningContent->enabled) {
        return response()->json([
            'message' => 'El contenido de aprendizaje ya está desactivado',
            'learning_content' => $learningContent
        ]);
    }

    if ($validated['activate']) {
        $learningContent->enabled = true;
    } else {
        $learningContent->enabled = false;
    }

    $learningContent->save();

    return response()->json([
        'message' => $validated['activate'] ? 'Contenido de aprendizaje publicado correctamente' : 'Contenido de aprendizaje archivado correctamente',
        'learning_content' => $learningContent
    ]);
}

}
