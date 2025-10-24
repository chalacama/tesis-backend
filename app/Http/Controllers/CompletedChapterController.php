<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Models\CompletedChapter;
use App\Models\Chapter;
use App\Models\Registration;
use App\Models\User;
use App\Models\Course;
use App\Models\LearningContent;
use App\Models\TypeLearningContent;
use Carbon\Carbon;
use Exception;

class CompletedChapterController extends Controller
{
     use AuthorizesRequests;
    public function updateContent(Request $request, Chapter $chapter)
    {
        $course = $chapter->module->course;

        if (!$course->enabled) {
            return response()->json([
                'ok'      => false,
                'message' => 'El curso no está activo.',
            ], 403);
        }

        // Política de acceso al curso
        $this->authorize('view', $course);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'ok'      => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        // Debe estar registrado al curso
        $isRegistered = Registration::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$isRegistered) {
            return response()->json([
                'ok'      => false,
                'message' => 'Solo los usuarios registrados al curso pueden completar capítulos.',
            ], 403);
        }

        // El capítulo debe tener LearningContent para registrar progreso de contenido
        $hasLearningContent = $chapter->learningContent()->exists();
        if (!$hasLearningContent) {
            return response()->json([
                'ok'      => false,
                'message' => 'Este capítulo no tiene contenido de aprendizaje (learning content).',
            ], 422);
        }

        // Validación de entrada: incremento (delta) de progreso
        $validator = Validator::make($request->all(), [
            'delta' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'delta.required' => 'El campo delta es requerido.',
            'delta.numeric'  => 'El campo delta debe ser numérico.',
            'delta.min'      => 'El incremento mínimo es 0.',
            'delta.max'      => 'El incremento máximo es 100.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Datos inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $delta = (float)$validator->validated()['delta'];
        $threshold = 70.0;

        $result = DB::transaction(function () use ($user, $chapter, $delta, $threshold) {
            // Asegura un registro único por (user_id, chapter_id)
            $cc = CompletedChapter::firstOrCreate(
                ['user_id' => $user->id, 'chapter_id' => $chapter->id],
                ['content_progress' => 0, 'content_at' => null, 'test_at' => null]
            );

            $before  = (float)$cc->content_progress;
            $after   = round(min(100.0, $before + $delta), 2);
            $now     = Carbon::now();
            $touched = false;
            $crossed = false;

            // Si cruza el umbral (de <70 a >=70) y aún no se había marcado content_at
            if ($before < $threshold && $after >= $threshold && is_null($cc->content_at)) {
                $cc->content_at = $now;
                $crossed = true;

                // Si NO hay test (no hay preguntas), marcamos también test_at
                $hasTest = $chapter->questions()->exists();
                if (!$hasTest && is_null($cc->test_at)) {
                    $cc->test_at = $now;
                }
            }

            if ($after !== $before) {
                $cc->content_progress = $after;
                $touched = true;
            }

            if ($touched || $crossed) {
                $cc->save();
            }

            return [
                'record'          => $cc->fresh(),
                'before'          => $before,
                'after'           => $after,
                'crossed70'       => $crossed,
                'autoCompletedTest' => $crossed && !$chapter->questions()->exists(),
            ];
        });

        return response()->json([
            'ok'      => true,
            'message' => 'Progreso actualizado correctamente.',
            'data'    => [
                'chapter_id'        => $chapter->id,
                'before'            => $result['before'],
                'after'             => $result['after'],
                'content_at'        => optional($result['record']->content_at)->toISOString(),
                'test_at'           => optional($result['record']->test_at)->toISOString(),
                'crossed70'         => $result['crossed70'],
                'autoCompletedTest' => $result['autoCompletedTest'],
            ],
        ], 200);
    }

    /**
     * (Para más adelante) Lógica de completar TEST.
     * Aquí solo verificamos curso/permiso; implementaremos luego tu regla para test_at.
     */
    public function updateTest(Request $request, Chapter $chapter)
    {
        $course = $chapter->module->course;

        if (!$course->enabled) {
            return response()->json([
                'ok'      => false,
                'message' => 'El curso no está activo.',
            ], 403);
        }

        $this->authorize('view', $course);

        return response()->json([
            'ok'      => false,
            'message' => 'Implementación pendiente para completar test.',
        ], 501);
    }
}
