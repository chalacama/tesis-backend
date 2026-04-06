<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Format;
use App\Models\LearningContent;
use App\Models\TypeLearningContent;
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\NewContentInCourseNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class LearningContentController extends Controller
{
    use AuthorizesRequests;

    // ── show ────────────────────────────────────────────────────────────────
    public function show(Chapter $chapter): JsonResponse
    {
        $course = $chapter->module?->course;
        $this->authorize('viewHidden', $course);

        $content = $chapter->learningContent()
            ->with([
                'typeLearningContent:id,name',
                'format:id,name,max_size_bytes,min_duration_seconds,max_duration_seconds',
            ])
            ->first();

        return response()->json($this->buildResponse($chapter, $content));
    }

    // ── update ──────────────────────────────────────────────────────────────
    public function update(Request $request, Chapter $chapter): JsonResponse
    {
        set_time_limit(300);

        $course = $chapter->module?->course;
        $this->authorize('update', $course);

        // 1. Validar IDs para poder cargar el formato con sus límites
        $ids = $request->validate([
            'type_content_id' => ['required', 'integer', 'exists:type_learning_contents,id'],
            'format_id'       => ['required', 'integer', 'exists:formats,id'],
        ]);

        // El formato debe pertenecer al tipo indicado y estar activo
        $format = Format::where('id', $ids['format_id'])
            ->where('type_learning_content_id', $ids['type_content_id'])
            ->where('enabled', true)
            ->first();

        if (! $format) {
            throw ValidationException::withMessages([
                'format_id' => ['El formato no es válido para el tipo seleccionado.'],
            ]);
        }

        // 2. Validar el resto con el límite de tamaño dinámico del formato
        $maxKb = $format->max_size_bytes ? (int) ceil($format->max_size_bytes / 1024) : 921_600;
        $data  = $request->validate([
            'url'       => ['nullable', 'string', 'max:2048'],
            'url_insert' => ['nullable', 'string', 'max:2048'],
            'file'      => ['nullable', 'file', "max:{$maxKb}"],
            'name'      => ['nullable', 'string', 'max:255'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $isNewContent    = false;
        $learningContent = null;

        try {
            DB::transaction(function () use (
                $request, $chapter, $course, $data, $ids, $format,
                &$isNewContent, &$learningContent
            ) {
                $type        = TypeLearningContent::findOrFail($ids['type_content_id']);
                $typeName    = strtolower(trim($type->name));
                $hasNewFile  = $request->hasFile('file') && $request->file('file')->isValid();
                $newUrl      = (isset($data['url']) && trim($data['url']) !== '') ? trim($data['url']) : null;
                $newUrlInsert = (isset($data['url_insert']) && trim($data['url_insert']) !== '') ? trim($data['url_insert']) : null;
                $newName     = null;
                $newSize     = null;
                $newDuration = isset($data['duration_seconds']) ? (int) $data['duration_seconds'] : null;

                $existing = LearningContent::where('chapter_id', $chapter->id)
                    ->with(['typeLearningContent:id,name', 'format:id,name,min_duration_seconds,max_duration_seconds,max_size_bytes'])
                    ->first();

                // ── Eliminar contenido si no hay ni fichero ni URL ──────────
                if (! $hasNewFile && $newUrl === null) {
                    if ($existing) {
                        $this->maybeDeleteFromGCS($existing);
                        $existing->delete();
                    }
                    $learningContent = null;
                    $isNewContent    = false;
                    return;
                }

                // ── Subir a GCS (solo tipo archive con fichero) ──────
                if ($hasNewFile) {
                    $file         = $request->file('file');
                    $newName      = $file->getClientOriginalName();
                    $newSize      = $file->getSize();
                    
                    // Validar tamaño
                    if ($format->max_size_bytes && $newSize > $format->max_size_bytes) {
                        throw ValidationException::withMessages([
                            'file' => ["El archivo excede el tamaño máximo de " . ($format->max_size_bytes / 1024 / 1024) . " MB"],
                        ]);
                    }

                    // Borrar archivo anterior si existe
                    if ($existing && $existing->url_insert) {
                        Storage::disk('gcs')->delete($existing->url_insert);
                    }

                    // Construir ruta en GCS: courses/{course_id}/chapters/{chapter_id}/content/{filename}
                    $gcsPath = "courses/{$course->id}/chapters/{$chapter->id}/content/{$newName}";
                    Storage::disk('gcs')->put($gcsPath, file_get_contents($file->getRealPath()));
                    $newUrl = Storage::disk('gcs')->url($gcsPath);
                    $newUrlInsert = $gcsPath;

                    // Extraer duración para video/audio usando FFmpeg
                    $formatName = strtolower($format->name);
                    if (in_array($formatName, ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv', 'mp3', 'wav', 'aac', 'flac'])) {
                        try {
                            $media = FFMpeg::fromDisk('local')->open($file->getRealPath());
                            $newDuration = (int) round($media->getDurationInSeconds());
                        } catch (\Throwable $e) {
                            Log::warning('FFmpeg duration extraction failed', [
                                'chapter_id' => $chapter->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                } else {
                    // Para LINK: conservar el nombre si viene en el request, si no usar el anterior
                    $newName = $data['name'] ?? $existing?->name;
                }

                // ── Validar duración ──────────────────────────────────────
                $this->validateDuration($format, $newDuration);

                // ── Crear o actualizar ──────────────────────────────────────
                $fields = [
                    'type_content_id'  => $type->id,
                    'format_id'        => $format->id,
                    'url'              => $newUrl,
                    'url_insert'       => $newUrlInsert,
                    'name'             => $newName,
                    'size_bytes'       => $newSize,
                    'duration_seconds' => $newDuration,
                ];

                if ($existing) {
                    $existing->fill($fields)->save();
                    $existing->load([
                        'typeLearningContent:id,name',
                        'format:id,name,max_size_bytes,min_duration_seconds,max_duration_seconds',
                    ]);
                    $learningContent = $existing;
                    $isNewContent    = false;
                } else {
                    $content = LearningContent::create(
                        array_merge($fields, ['chapter_id' => $chapter->id])
                    );
                    $content->load([
                        'typeLearningContent:id,name',
                        'format:id,name,max_size_bytes,min_duration_seconds,max_duration_seconds',
                    ]);
                    $learningContent = $content;
                    $isNewContent    = true;
                }
            });

            if ($isNewContent && $course && $learningContent) {
                $this->notifyRegisteredUsers($course, $chapter, $learningContent, Auth::user());
            }

            return response()->json($this->buildResponse($chapter, $learningContent));

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('LearningContent update error', [
                'chapter_id' => $chapter->id,
                'error'      => $e->getMessage(),
            ]);
            return response()->json(['ok' => false, 'error' => 'No se pudo actualizar el contenido.'], 500);
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Respuesta unificada: contenido actual + todos los tipos/formatos activos.
     * El frontend nunca necesita una segunda llamada para los tipos.
     */
    private function buildResponse(Chapter $chapter, ?LearningContent $content): array
    {
        $types = TypeLearningContent::where('enabled', true)
            ->with(['formats' => fn ($q) => $q
                ->where('enabled', true)
                ->select(
                    'id', 'name',
                    'max_size_bytes', 'min_duration_seconds', 'max_duration_seconds',
                    'type_learning_content_id'
                )
                ->orderBy('id'),
            ])
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        return [
            'ok'               => true,
            'chapter_id'       => $chapter->id,
            'learning_content' => $content ? $this->serializeContent($content) : null,
            'types'            => $types,
        ];
    }

    private function serializeContent(LearningContent $lc): array
    {
        $lc->loadMissing([
            'typeLearningContent:id,name',
            'format:id,name,max_size_bytes,min_duration_seconds,max_duration_seconds',
        ]);

        return [
            'id'               => $lc->id,
            'name'             => $lc->name,
            'url'              => $lc->url,
            'url_insert'       => $lc->url_insert,
            'size_bytes'       => $lc->size_bytes,
            'duration_seconds' => $lc->duration_seconds,
            'type_content_id'  => $lc->type_content_id,
            'format_id'        => $lc->format_id,
            'created_at'       => optional($lc->created_at)->toISOString(),
            'updated_at'       => optional($lc->updated_at)->toISOString(),
            'type_learning_content' => $lc->typeLearningContent
                ? ['id' => $lc->typeLearningContent->id, 'name' => $lc->typeLearningContent->name]
                : null,
            'format' => $lc->format ? [
                'id'                   => $lc->format->id,
                'name'                 => $lc->format->name,
                'max_size_bytes'       => $lc->format->max_size_bytes,
                'min_duration_seconds' => $lc->format->min_duration_seconds,
                'max_duration_seconds' => $lc->format->max_duration_seconds,
            ] : null,
        ];
    }

    /**
     * Borra el asset de GCS del contenido anterior.
     * Solo actúa si el contenido previo era de tipo archive (tiene url_insert).
     */
    private function maybeDeleteFromGCS(LearningContent $existing): void
    {
        if (! $existing->url_insert) return;

        try {
            Storage::disk('gcs')->delete($existing->url_insert);
        } catch (\Throwable $e) {
            Log::warning('GCS delete failed', [
                'learning_content_id' => $existing->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Valida la duración contra los límites del formato.
     */
    private function validateDuration(Format $format, ?int $duration): void
    {
        if (! $format->min_duration_seconds && ! $format->max_duration_seconds) return;

        if ($duration === null) {
            Log::warning('No se pudo obtener duración del archivo', ['format' => $format->name]);
            return;
        }

        $error = null;
        if ($format->min_duration_seconds && $duration < $format->min_duration_seconds) {
            $error = "Duración mínima: {$format->min_duration_seconds} s. Detectado: {$duration} s.";
        } elseif ($format->max_duration_seconds && $duration > $format->max_duration_seconds) {
            $error = "Duración máxima: {$format->max_duration_seconds} s. Detectado: {$duration} s.";
        }

        if ($error) {
            throw ValidationException::withMessages(['file' => [$error]]);
        }
    }

    private function notifyRegisteredUsers(
        Course $course,
        Chapter $chapter,
        LearningContent $content,
        ?User $actor = null
    ): void {
        $userIds = Registration::where('course_id', $course->id)
            ->pluck('user_id')->unique()->values()->all();

        if (empty($userIds)) return;

        $content->loadMissing('typeLearningContent');

        User::whereIn('id', $userIds)->get()->each(function (User $student) use (
            $course, $chapter, $content, $actor
        ) {
            if ($actor && $actor->id === $student->id) return;
            $student->notify(new NewContentInCourseNotification($course, $chapter, $content, $actor));
        });
    }
}