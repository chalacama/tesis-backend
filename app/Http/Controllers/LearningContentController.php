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
use Cloudinary\Cloudinary;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LearningContentController extends Controller
{
    use AuthorizesRequests;

    // Cloudinary resource_type por familia de formato
    private const CLOUDINARY_VIDEO = ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv', 'mp3', 'wav', 'aac', 'flac'];
    private const CLOUDINARY_IMAGE = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
    // todo lo demás → 'raw' (pdf, docx, xlsx, pptx, zip, rar, txt …)

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
            'url'  => ['nullable', 'string', 'max:2048'],
            'file' => ['nullable', 'file', "max:{$maxKb}"],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $isNewContent    = false;
        $learningContent = null;

        try {
            DB::transaction(function () use (
                $request, $chapter, $data, $ids, $format,
                &$isNewContent, &$learningContent
            ) {
                $type        = TypeLearningContent::findOrFail($ids['type_content_id']);
                $typeName    = strtolower(trim($type->name));
                $hasNewFile  = $request->hasFile('file') && $request->file('file')->isValid();
                $newUrl      = (isset($data['url']) && trim($data['url']) !== '') ? trim($data['url']) : null;
                $newName     = $data['name'] ?? null;
                $newSize     = null;
                $newDuration = null;

                $existing = LearningContent::where('chapter_id', $chapter->id)
                    ->with(['typeLearningContent:id,name', 'format:id,name'])
                    ->first();

                // ── Eliminar contenido si no hay ni fichero ni URL ──────────
                if (! $hasNewFile && $newUrl === null) {
                    if ($existing) {
                        $this->maybeDeleteFromCloudinary($chapter, $existing);
                        $existing->delete();
                    }
                    $learningContent = null;
                    $isNewContent    = false;
                    return;
                }

                // ── Subir a Cloudinary (solo tipo archive con fichero) ──────
                if ($hasNewFile) {
                    $file         = $request->file('file');
                    $newName      = $newName ?? $file->getClientOriginalName();
                    $newSize      = $file->getSize();
                    $resourceType = $this->cloudinaryResourceType($format->name);

                    // Borrar anterior si cambia resource_type
                    if ($existing) {
                        $this->maybeDeleteFromCloudinary($chapter, $existing, $resourceType);
                    }

                    $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
                    $upload = $cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'folder'        => 'chapters',
                            'public_id'     => (string) $chapter->id,
                            'overwrite'     => true,
                            'resource_type' => $resourceType,
                            'invalidate'    => true,
                        ]
                    );

                    $newUrl = $upload['secure_url'] ?? $upload['url'] ?? null;

                    // Duración solo para video/audio (viene en la respuesta de Cloudinary)
                    if (isset($upload['duration']) && is_numeric($upload['duration'])) {
                        $newDuration = (int) round((float) $upload['duration']);
                    }

                    // Validar duración server-side (belt-and-suspenders, cliente ya validó)
                    $this->validateDuration($cloudinary, $chapter, $format, $resourceType, $newDuration);
                }

                // ── Crear o actualizar ──────────────────────────────────────
                $fields = [
                    'type_content_id'  => $type->id,
                    'format_id'        => $format->id,
                    'url'              => $newUrl,
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

    /** Devuelve el resource_type de Cloudinary según el nombre del formato. */
    private function cloudinaryResourceType(string $formatName): string
    {
        $name = strtolower(trim($formatName));
        if (in_array($name, self::CLOUDINARY_VIDEO, true)) return 'video';
        if (in_array($name, self::CLOUDINARY_IMAGE, true)) return 'image';
        return 'raw';
    }

    /**
     * Borra el asset de Cloudinary del contenido anterior cuando es necesario.
     * Solo actúa si el contenido previo era de tipo archive (tiene archivo físico).
     *
     * @param  string|null  $incomingResourceType  Si coincide con el antiguo, Cloudinary
     *                                              lo sobreescribirá solo (overwrite=true).
     */
    private function maybeDeleteFromCloudinary(
        Chapter $chapter,
        LearningContent $existing,
        ?string $incomingResourceType = null
    ): void {
        $oldTypeName = strtolower($existing->typeLearningContent?->name ?? '');
        if ($oldTypeName !== 'archive' || ! $existing->url) return;

        $oldResourceType = $this->cloudinaryResourceType($existing->format?->name ?? '');

        // Mismo resource_type → Cloudinary lo sobreescribe con overwrite:true, no borrar
        if ($incomingResourceType !== null && $incomingResourceType === $oldResourceType) return;

        try {
            (new Cloudinary(config('cloudinary.cloud_url')))
                ->uploadApi()
                ->destroy("chapters/{$chapter->id}", [
                    'resource_type' => $oldResourceType,
                    'invalidate'    => true,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Cloudinary delete failed', [
                'chapter_id' => $chapter->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Valida la duración contra los límites del formato.
     * Si falla, intenta eliminar el archivo ya subido y lanza ValidationException.
     */
    private function validateDuration(
        Cloudinary $cloudinary,
        Chapter $chapter,
        Format $format,
        string $resourceType,
        ?int $duration
    ): void {
        if (! $format->min_duration_seconds && ! $format->max_duration_seconds) return;

        if ($duration === null) {
            Log::warning('No se pudo obtener duración del archivo', [
                'chapter_id' => $chapter->id, 'format' => $format->name,
            ]);
            return; // El cliente ya validó; no bloqueamos si Cloudinary no devuelve duración
        }

        $error = null;
        if ($format->min_duration_seconds && $duration < $format->min_duration_seconds) {
            $error = "Duración mínima: {$format->min_duration_seconds} s. Detectado: {$duration} s.";
        } elseif ($format->max_duration_seconds && $duration > $format->max_duration_seconds) {
            $error = "Duración máxima: {$format->max_duration_seconds} s. Detectado: {$duration} s.";
        }

        if ($error) {
            try {
                $cloudinary->uploadApi()->destroy("chapters/{$chapter->id}", [
                    'resource_type' => $resourceType, 'invalidate' => true,
                ]);
            } catch (\Throwable) {}

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