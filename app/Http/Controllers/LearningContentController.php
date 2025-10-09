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
        'file'            => ['nullable', 'file'], // añade max:size / mimes si lo necesitas
    ]);

    try {
        return DB::transaction(function () use ($request, $chapter, $data) {

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

                    // public_id final quedará como "archives/chapter/{id}"
                    $upload = $cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'folder'          => 'archives',
                            'public_id'       => "chapter/{$chapter->id}",
                            'overwrite'       => true,
                            'resource_type'   => 'auto', // soporta image/video/pdf
                            'use_filename'    => true,
                            'unique_filename' => false,
                            'transformation'  => [
                                ['quality' => 'auto:good'],
                                ['fetch_format' => 'auto'],
                            ],
                        ]
                    );

                    $newUrl = $upload['secure_url'] ?? $upload['url'] ?? null;
                }
                // Si no hay file, se respetará $newUrl (puede ser null para archivar)
            }

            // Buscar contenido existente (incluyendo soft-deleted)
            $existing = LearningContent::withTrashed()
                ->where('chapter_id', $chapter->id)
                ->first();

            // Reglas para archivar (soft delete) automáticamente:
            // - YOUTUBE sin URL
            // - ARCHIVO sin file y sin URL
            $shouldArchive =
                ($typeName === 'youtube' && is_null($newUrl)) ||
                ($typeName === 'archivo'
                    && (!($request->hasFile('file') && $request->file('file')->isValid()))
                    && is_null($newUrl));

            if ($shouldArchive) {
                if ($existing && is_null($existing->deleted_at)) {
                    // Marcar como borrado lógico; la purga física la hará Prunable (p. ej. a 30 días)
                    $existing->delete();
                }

                return response()->json([
                    'ok'               => true,
                    'chapter_id'       => $chapter->id,
                    'learning_content' => null,
                ]);
            }

            // Si NO se archiva, crear/actualizar (restaurando si estaba en papelera)
            if ($existing) {
                if (!is_null($existing->deleted_at)) {
                    $existing->restore();
                }

                $existing->fill([
                    'type_content_id' => $type->id,
                    'url'             => $newUrl,
                ])->save();

                $content = $existing;
            } else {
                $content = LearningContent::create([
                    'chapter_id'      => $chapter->id,
                    'type_content_id' => $type->id,
                    'url'             => $newUrl,
                ]);
            }

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




}
