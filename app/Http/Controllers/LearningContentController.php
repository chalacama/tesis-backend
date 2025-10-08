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
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\TutorInvitationEmail;
use Exception;

class LearningContentController extends Controller
{
    use AuthorizesRequests;
public function show(Chapter $chapter): JsonResponse
{
        // Autoriza contra el curso dueño del capítulo
        $course = $chapter->module?->course;
        $this->authorize('viewHidden', $course);

        // Carga el contenido (si hay) con su tipo, seleccionando solo columnas necesarias
        $content = $chapter->learningContent()
            ->select('id', 'url', 'type_content_id', 'created_at', 'updated_at')
            ->with([
                'typeLearningContent:id,name,max_size_mb,min_duration_seconds,max_duration_seconds,created_at,updated_at'
            ])
            ->first(); // puede ser null si el capítulo aún no tiene contenido

        // Devolvemos sólo lo que necesitas para el tab "Contenido"
        return response()->json([
            'ok'               => true,
            'chapter_id'       => $chapter->id,
            'learning_content' => $content, // null si no existe
        ]);
}

public function update(Request $request, Chapter $chapter): JsonResponse
{
     set_time_limit(300);
    $course = $chapter->module?->course;
    $this->authorize('update', $course);

    // Validación básica
    $data = $request->validate([
        'type_content_id' => ['required', 'integer', 'exists:type_learning_contents,id'],
        'url'             => ['nullable', 'string'],
        'file'            => ['nullable', 'file'], // puedes añadir max:size según tu política
    ]);

    try {
        return DB::transaction(function () use ($request, $chapter, $data) {

            $type = TypeLearningContent::query()
                ->select('id','name')
                ->findOrFail($data['type_content_id']);

            $typeName = strtolower(trim($type->name ?? ''));

            $newUrl = $data['url'] ?? null;

            // Si es ARCHIVO y viene file -> sube a Cloudinary
            if ($typeName === 'archivo') {
                if ($request->hasFile('file') && $request->file('file')->isValid()) {
                    $file = $request->file('file');

                    $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
                    $publicId   = "chapter/{$chapter->id}";

                    // Para soportar videos (mp4) y PDF, usa resource_type 'auto'
                    $upload = $cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'folder'         => 'archives',
                            'public_id'      => $publicId,
                            'overwrite'      => true,
                            'resource_type'  => 'auto',
                            'use_filename'   => true,
                            'unique_filename'=> false,
                            'transformation' => [
                                ['quality' => 'auto:good'],
                                ['fetch_format' => 'auto'],
                            ],
                        ]
                    );

                    $newUrl = $upload['secure_url'] ?? $upload['url'] ?? null;
                }
                // Si no hay file: usamos la url entrante (que puede ser null para limpiar)
            }

            // Si es YOUTUBE: no sube archivo, solo usa la URL (puede ser null para limpiar)
            // if ($typeName === 'youtube') { $newUrl ya viene del request }

            // UPSERT
            $content = LearningContent::query()->updateOrCreate(
                ['chapter_id' => $chapter->id],
                [
                    'type_content_id' => $type->id,
                    'url'             => $newUrl,  // puede ser null
                ]
            );

            // Respuesta consistente con show()
            $content->load([
                'typeLearningContent:id,name,max_size_mb,min_duration_seconds,max_duration_seconds,created_at,updated_at'
            ]);

            return response()->json([
                'ok'               => true,
                'chapter_id'       => $chapter->id,
                'learning_content' => [
                    'id'                    => $content->id,
                    'url'                   => $content->url,
                    'type_content_id'       => $content->type_content_id,
                    'created_at'            => $content->created_at,
                    'updated_at'            => $content->updated_at,
                    // Laravel serializa snake_case para relaciones: type_learning_content
                    'type_learning_content' => $content->getRelation('typeLearningContent'),
                ],
            ]);
        });
    } catch (ValidationException $e) {
        throw $e;
    } catch (\Throwable $e) {
        Log::error('LearningContent update error', [
            'chapter_id' => $chapter->id,
            'error'      => $e->getMessage(),
        ]);

        return response()->json([
            'ok'    => false,
            'error' => 'No se pudo actualizar el contenido.',
        ], 500);
    }
}

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

private function storeCloud(CreateVideoCloudinaryRequest $request)
    {
        set_time_limit(300);

        try {
            $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
            $upload = $cloudinary->uploadApi()->upload(
                $request->validated()['file']->getRealPath(),
                [
                    'resource_type' => 'video',
                    'folder' => 'archives',
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
            'type_content_id' => $request->validated()['type_content_id'],
            'chapter_id' => $request->validated()['chapter_id'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $content,//$signedUrl
        ], 201);
    }
private function destroyCloud($id)
    {
        // 1. Encuentra el registro en la BD o falla con un error 404
        $content = LearningContent::findOrFail($id);

        try {
            // 2. Extraer el 'public_id' de la URL del video.
            // Esta es la parte más importante. Cloudinary necesita este ID.
            // Asumimos que la carpeta de subida fue 'learning_content/videos'.
            $filename = pathinfo($content->url, PATHINFO_FILENAME);
            $publicId = 'archives/' . $filename;

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
private function archived($id)
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
