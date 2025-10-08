<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\Module;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Models\Course;
//  $course = $chapter->module?->course;
class ChapterController extends Controller
{
    use AuthorizesRequests;

    public function show(Chapter $chapter): JsonResponse
    {
        // Cargar solo lo necesario para autorizar
        $chapter->load(['module:id,course_id']);

        // Autorizar con la policy del curso (reglas únicas centralizadas)
        $course = Course::findOrFail($chapter->module->course_id);
        $this->authorize('viewHidden', $course);

        // Tomar únicamente los campos del capítulo (sin relaciones)
        $payload = $chapter->only([
            'id',
            'title',
            'description',
            'order',
            'module_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        return response()->json([
            'ok'      => true,
            'chapter' => $payload,
        ]);
    }

    public function update(Request $request, Chapter $chapter): JsonResponse
    {
        // Cargar lo mínimo para autorizar
        $chapter->load(['module:id,course_id']);
        $course = Course::findOrFail($chapter->module->course_id);

        // El usuario debe poder actualizar el curso
        $this->authorize('update', $course);

        // Validación: solo campos de "detalles"
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        // Actualizar el capítulo
        $chapter->update([
            'title'       => trim($data['title']),
            'description' => $data['description'] ?? null,
        ]);

        // Responder solo con los campos del capítulo (ligero)
        $payload = $chapter->only([
            'id',
            'title',
            'description',
            'order',
            'module_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        return response()->json([
            'ok'      => true,
            'chapter' => $payload,
        ]);
    }


}
