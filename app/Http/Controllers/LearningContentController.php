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
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\NewContentInCourseNotification;


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

    // ⚠ NO TOCAMOS VALIDACIÓN (Angular ya depende de esto)
    $data = $request->validate([
        'type_content_id' => ['required', 'integer', 'exists:type_learning_contents,id'],
        'url'             => ['nullable', 'string'],
        'file'            => ['nullable', 'file'], // añade max:size / mimes si lo necesitas
    ]);

    // Flags para saber si hay contenido nuevo
    $isNewContent    = false;
    $learningContent = null;

    try {
        DB::transaction(function () use ($request, $chapter, $data, &$isNewContent, &$learningContent) {

            // Tipo de contenido
            $type = TypeLearningContent::query()
                ->select('id', 'name')
                ->findOrFail($data['type_content_id']);

            $typeName = strtolower(trim($type->name ?? ''));

            // Normaliza URL ('' -> null)
            $newUrl = isset($data['url']) && trim($data['url']) !== '' ? trim($data['url']) : null;

            // Si es ARCHIVO y viene file -> sube a Cloudinary y obtiene URL
            if ($typeName === 'archivo') {
                if ($request->hasFile('file') && $request->file('file')->isValid()) {
                    $file = $request->file('file');

                    $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));

                    // public_id final quedará como algo tipo "archives/chapter/{id}"
                    $upload = $cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'folder'          => 'archives',
                            'public_id'       => "chapter/{$chapter->id}",
                            'overwrite'       => true,
                            'resource_type'   => 'auto', // soporta image/video/pdf
                            'use_filename'    => true,
                            'unique_filename' => false,
                        ]
                    );

                    $newUrl = $upload['secure_url'] ?? $upload['url'] ?? null;
                }
                // Si no hay file, se respetará $newUrl (puede ser null para eliminar contenido)
            }

            // Buscar contenido existente (solo registros reales, ya no hay soft delete)
            $existing = LearningContent::where('chapter_id', $chapter->id)->first();

            // Reglas para ELIMINAR automáticamente:
            // - YOUTUBE sin URL
            // - ARCHIVO sin file y sin URL
            $shouldDelete =
                ($typeName === 'youtube' && is_null($newUrl)) ||
                ($typeName === 'archivo'
                    && (!($request->hasFile('file') && $request->file('file')->isValid()))
                    && is_null($newUrl));

            if ($shouldDelete) {
                if ($existing) {
                    // Si el contenido está/estaba asociado a un archivo, intentamos borrar de Cloudinary
                    try {
                        // Asumimos convención "archives/chapter/{id}"
                        $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
                        $publicId   = "archives/chapter/{$chapter->id}";

                        $cloudinary->uploadApi()->destroy($publicId, [
                            'resource_type' => 'auto',
                            'invalidate'    => true,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('No se pudo eliminar el archivo de Cloudinary al borrar LearningContent', [
                            'chapter_id' => $chapter->id,
                            'error'      => $e->getMessage(),
                        ]);
                        // No lanzamos excepción: preferimos que la operación de BD se complete
                    }

                    // Eliminamos definitivamente el registro (ya no hay softDeletes)
                    $existing->delete();
                }

                // No hay contenido activo
                $learningContent = null;
                $isNewContent    = false;

                return;
            }

            // Si NO se elimina, crear/actualizar contenido
            if ($existing) {
                // Actualización normal de un contenido ya existente
                $existing->fill([
                    'type_content_id' => $type->id,
                    'url'             => $newUrl,
                ])->save();

                $existing->load([
                    'typeLearningContent:id,name,max_size_mb,min_duration_seconds,max_duration_seconds,created_at,updated_at'
                ]);

                $learningContent = $existing;
                // Ya existía -> NO lo contamos como "nuevo" para notificaciones
                $isNewContent = false;
            } else {
                // Crear nuevo contenido
                $content = LearningContent::create([
                    'chapter_id'      => $chapter->id,
                    'type_content_id' => $type->id,
                    'url'             => $newUrl,
                ]);

                $content->load([
                    'typeLearningContent:id,name,max_size_mb,min_duration_seconds,max_duration_seconds,created_at,updated_at'
                ]);

                $learningContent = $content;
                $isNewContent    = true;
            }
        });

        // 🔔 Fuera de la transacción: notificar si realmente hay contenido nuevo
        if ($isNewContent && $course && $learningContent) {
            $this->notifyRegisteredUsersNewContent($course, $chapter, $learningContent, Auth::user());
        }

        return response()->json([
            'ok'               => true,
            'chapter_id'       => $chapter->id,
            'learning_content' => $learningContent ? [
                'id'                    => $learningContent->id,
                'url'                   => $learningContent->url,
                'type_content_id'       => $learningContent->type_content_id,
                'created_at'            => $learningContent->created_at,
                'updated_at'            => $learningContent->updated_at,
                'type_learning_content' => $learningContent->getRelation('typeLearningContent'),
            ] : null,
        ]);
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
protected function notifyRegisteredUsersNewContent(
    Course $course,
    Chapter $chapter,
    LearningContent $content,
    ?User $actor = null
): void {
    // IDs de usuarios registrados en el curso
    $userIds = Registration::query()
        ->where('course_id', $course->id)
        ->pluck('user_id')
        ->unique()
        ->values()
        ->all();

    if (empty($userIds)) {
        return;
    }

    $students = User::query()
        ->whereIn('id', $userIds)
        ->get();

    // Aseguramos tener typeLearningContent cargado
    $content->loadMissing('typeLearningContent');

    foreach ($students as $student) {
        // Opcional: no notificar al mismo que agregó el contenido si también está registrado
        if ($actor && $actor->id === $student->id) {
            continue;
        }

        $student->notify(new NewContentInCourseNotification(
            $course,
            $chapter,
            $content,
            $actor
        ));
    }
}


}
