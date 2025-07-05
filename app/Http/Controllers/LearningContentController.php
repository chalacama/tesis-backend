<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LearningContent;
use App\Models\TypeLearningContent;
use Cloudinary\Api\Admin\AdminApi;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Validation\ValidationException;
use Cloudinary\Cloudinary;
use Exception;
class LearningContentController extends Controller
{
    
    public function createVideoCloudinary(Request $request)
{
    // 1. Validar la petición (esto se queda igual)
    $data = $request->validate([
        'file'           => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo',
        'chapter_id'     => 'required|integer|exists:chapters,id',
        'type_content_id'=> 'required|integer|exists:type_learning_contents,id',
    ]);

    $type = TypeLearningContent::findOrFail($data['type_content_id']);

    if ($type->name !== 'cloudinary' || !$type->enabled) {
        throw ValidationException::withMessages([
            'type_content_id' => ['El tipo de contenido debe ser "cloudinary" y estar habilitado.']
        ]);
    }

    // --- INICIO DE CAMBIOS ---

        // 3. Validar el tamaño del archivo usando el nuevo campo 'max_size_mb'
        // Se convierte el valor de la BD (que puede ser decimal) a float.
        $maxMB = (float) $type->max_size_mb;
        $maxKB = $maxMB * 1024;

        if (($request->file('file')->getSize() / 1024) > $maxKB) {
            throw ValidationException::withMessages([
                // Mensaje de error actualizado para mostrar el valor correcto.
                'file' => ["El video supera el tamaño máximo de {$type->max_size_mb}MB."]
            ]);
        }

        // 4. Validar la duración del video ANTES de subirlo
        try {
            // Obtener la ruta real del archivo temporal subido
            $videoPath = $request->file('file')->getRealPath();
            
            // Usar ffprobe para obtener la duración del video en segundos.
            // ffprobe debe estar instalado en el servidor y accesible a través de la línea de comandos.
            // El comando escapa la ruta del archivo para seguridad.
            $command = 'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . escapeshellarg($videoPath);
            $durationOutput = shell_exec($command);
            
            // Si ffprobe falla o no está instalado, $durationOutput será null o vacío.
            if (!$durationOutput) {
                 return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la duración del video. Asegúrate de que FFmpeg (ffprobe) esté instalado en el servidor.'
                ], 500);
            }

            $videoDuration = (float) $durationOutput;

            // Validar duración mínima, si está definida
            if ($type->min_duration_seconds !== null && $videoDuration < (float) $type->min_duration_seconds) {
                throw ValidationException::withMessages([
                    'file' => ["La duración del video ({$videoDuration}s) es menor a la mínima permitida ({$type->min_duration_seconds}s)."]
                ]);
            }

            // Validar duración máxima, si está definida
            if ($type->max_duration_seconds !== null && $videoDuration > (float) $type->max_duration_seconds) {
                 throw ValidationException::withMessages([
                    'file' => ["La duración del video ({$videoDuration}s) supera la máxima permitida ({$type->max_duration_seconds}s)."]
                ]);
            }

        } catch (Exception $e) {
            // Capturar cualquier excepción durante la validación de duración
             return response()->json([
                'success' => false,
                'message' => 'Error al procesar la duración del video: ' . $e->getMessage()
            ], 500);
        }

        // --- FIN DE CAMBIOS ---


    try {
        // 2. Instanciar Cloudinary manualmente con la config que SÍ funciona
        $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));

        // 3. Subir el archivo usando la API de subida
        $result = $cloudinary->uploadApi()->upload(
            $request->file('file')->getRealPath(),
            [
                'resource_type' => 'video',
                'folder'        => 'learning_content/videos',
            ]
        );

    } catch (Exception $e) {
        // Si algo falla en la subida, devuelve un error claro
        return response()->json([
            'success' => false,
            'message' => 'Error al subir el archivo a Cloudinary: ' . $e->getMessage()
        ], 500);
    }

    // 4. Registrar en la base de datos
    $content = LearningContent::create([
        'url'             => $result['secure_url'], // La URL viene en el índice 'secure_url'
        'enabled'         => true,
        'type_content_id' => $type->id,
        'chapter_id'      => $data['chapter_id'],
    ]);

    // 5. Responder con éxito
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

}
