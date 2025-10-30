<?php

namespace App\Http\Controllers;

use App\Models\CompletedChapter;
use App\Models\LearningContent;
use App\Models\ContentView;
use App\Models\TestView;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Registration;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompletedChapterController extends Controller
{
    use AuthorizesRequests;

    /**
     * POST /progress/{learningContent}/update
     * Body:
     * - progress: number [0..100] incremento que se suma al progreso actual (tope 100)
     */
    public function updateProgress(Request $request, LearningContent $learningContent)
    {
        // 1) Curso y autorización
        $chapter = $learningContent->chapter;
        $module  = $chapter->module;
        $course  = $module->course;

        $this->authorize('view', $course);

        if (!$course->enabled) {
            return response()->json(['ok' => false, 'message' => 'El curso no está activo.'], 403);
        }

        // 2) Verificar registro del usuario en el curso
        $userId = Auth::id();
        $isRegistered = Registration::query()
            ->where('course_id', $course->id)
            ->where('user_id', $userId)
            ->exists();

        if (!$isRegistered) {
            return response()->json(['ok' => false, 'message' => 'Debe estar registrado en el curso para actualizar progreso.'], 403);
        }

        // 3) Validación del payload (sin second_seen)
        $data = $request->validate([
            'progress' => ['required','numeric','min:0','max:100'],
        ]);
        $deltaProgress = (float) $data['progress'];

        // 4) Transacción principal
        $resultPayload = DB::transaction(function () use (
            $learningContent, $chapter, $course, $userId, $deltaProgress
        ) {
            // 4.1) Obtener/crear ContentView (sin tocar second_seen)
            /** @var ContentView $view */
            $view = ContentView::query()
                ->where('user_id', $userId)
                ->where('learning_content_id', $learningContent->id)
                ->lockForUpdate()
                ->first();

            if (!$view) {
                $view = new ContentView([
                    'user_id' => $userId,
                    'learning_content_id' => $learningContent->id,
                    'progress' => 0,
                ]);
            }

            $before = (float) ($view->progress ?? 0);
            $after  = min(100.0, round($before + $deltaProgress, 2));
            $crossed70 = $before < 70.0 && $after >= 70.0;

            $view->progress = $after;

            if ($crossed70 && is_null($view->completed_at)) {
                $view->completed_at = now();
            }

            $view->save();

            $contentAt = $view->completed_at ? Carbon::parse($view->completed_at) : null;

            // 4.2) Si no hay preguntas, crear TestView automático al completar contenido
            $autoCompletedTest = false;
            $hasQuestions = $chapter->questions()->exists();
            if (!$hasQuestions && $contentAt && !TestView::query()
                    ->where('user_id', $userId)
                    ->where('chapter_id', $chapter->id)
                    ->exists()) {
                $tv = new TestView([
                    'user_id'   => $userId,
                    'chapter_id'=> $chapter->id,
                ]);
                $tv->save();
                $autoCompletedTest = true;
            }

            // 4.3) test_at (último intento)
            $testAt = TestView::query()
                ->where('user_id', $userId)
                ->where('chapter_id', $chapter->id)
                ->latest('created_at')
                ->value('created_at');
            $testAt = $testAt ? Carbon::parse($testAt) : null;

            // 4.4) Intentar completar el capítulo y emitir certificado si aplica
            $chapterCompleted   = $this->ensureChapterCompletion($chapter, $userId);
            $certificateIssued  = $this->issueCertificateIfEligible($course, $userId);

            return [
                'ok' => true,
                'message' => 'Progreso actualizado correctamente.',
                'data' => [
                    'chapter_id'         => $chapter->id,
                    'before'             => $before,
                    'after'              => $after,
                    'content_at'         => $contentAt ? $contentAt->toIso8601String() : null,
                    'test_at'            => $testAt ? $testAt->toIso8601String() : null,
                    'crossed70'          => $crossed70,
                    'autoCompletedTest'  => $autoCompletedTest,
                    'chapter_completed'  => $chapterCompleted,
                    'certificate_issued' => $certificateIssued,
                ],
            ];
        });

        return response()->json($resultPayload);
    }

    /**
     * Marca un capítulo como completado si hay evidencia válida (content y test)
     * posterior a la "versión" vigente del capítulo.
     */
    private function ensureChapterCompletion(Chapter $chapter, int $userId): bool
    {
        $versionAt = $this->chapterVersionAt($chapter); // Carbon|null

        // content_at
        $contentAt = null;
        if ($chapter->learningContent) {
            $contentAt = ContentView::query()
                ->where('user_id', $userId)
                ->where('learning_content_id', $chapter->learningContent->id)
                ->value('completed_at');
            $contentAt = $contentAt ? Carbon::parse($contentAt) : null;
        } else {
            $contentAt = TestView::query()
                ->where('user_id', $userId)
                ->where('chapter_id', $chapter->id)
                ->value('completed_at');
            $contentAt = $contentAt ? Carbon::parse($contentAt) : null;
        }

        // test_at
        $testAt = null;
        if ($chapter->questions()->exists()) {
            $testAt = TestView::query()
                ->where('user_id', $userId)
                ->where('chapter_id', $chapter->id)
                ->value('completed_at');
            $testAt = $testAt ? Carbon::parse($testAt) : null;
        } else {
            $testAt = ContentView::query()
                ->where('user_id', $userId)
                ->whereHas('learningContent', fn($q) => $q->where('chapter_id', $chapter->id))
                ->value('completed_at');
            $testAt = $testAt ? Carbon::parse($testAt) : null;
        }

        if (!$contentAt || !$testAt) {
            return false;
        }

        if ($versionAt) {
            if ($contentAt->lt($versionAt) || $testAt->lt($versionAt)) {
                return false;
            }
        }

        $already = CompletedChapter::query()
            ->where('user_id', $userId)
            ->where('chapter_id', $chapter->id)
            ->when($versionAt, fn($q) => $q->where('created_at', '>=', $versionAt))
            ->exists();

        if ($already) {
            return true;
        }

        $doneAt = $contentAt->greaterThan($testAt) ? $contentAt : $testAt;

        $cc = new CompletedChapter([
            'user_id'    => $userId,
            'chapter_id' => $chapter->id,
        ]);
        $cc->created_at = $doneAt;
        $cc->updated_at = $doneAt;
        $cc->save();

        return true;
    }

    /**
     * Versión del capítulo = max(created_at del LearningContent, created_at de última pregunta)
     */
    private function chapterVersionAt(Chapter $chapter): ?Carbon
    {
        $lcCreated = $chapter->learningContent?->created_at
            ? Carbon::parse($chapter->learningContent->created_at)
            : null;

        $lastQ = $chapter->questions()->max('created_at');
        $qCreated = $lastQ ? Carbon::parse($lastQ) : null;

        if ($lcCreated && $qCreated) {
            return $lcCreated->greaterThan($qCreated) ? $lcCreated : $qCreated;
        }
        return $lcCreated ?: $qCreated;
    }

    /**
     * Emite certificado si todos los capítulos están válidamente completados
     * para la versión vigente del curso. No falla si no existe el modelo.
     */
    private function issueCertificateIfEligible(Course $course, int $userId): bool
    {
        $certificateModel = 'App\\Models\\CourseCertificate';
        if (!class_exists($certificateModel)) {
            return false;
        }

        $isRegistered = Registration::query()
            ->where('course_id', $course->id)
            ->where('user_id', $userId)
            ->exists();

        if (!$isRegistered) {
            return false;
        }

        $chapters = Chapter::query()
            ->whereIn('module_id', $course->modules()->pluck('id'))
            ->get();

        if ($chapters->isEmpty()) {
            return false;
        }

        $globalVersion = null;
        foreach ($chapters as $ch) {
            $versionAt = $this->chapterVersionAt($ch);
            if ($versionAt) {
                $globalVersion = $globalVersion
                    ? ($versionAt->greaterThan($globalVersion) ? $versionAt : $globalVersion)
                    : $versionAt;
            }

            $hasValid = CompletedChapter::query()
                ->where('user_id', $userId)
                ->where('chapter_id', $ch->id)
                ->when($versionAt, fn($q) => $q->where('created_at', '>=', $versionAt))
                ->exists();

            if (!$hasValid) {
                return false;
            }
        }

        $globalVersion = $globalVersion ?: now()->subSecond();

        $certClass = new $certificateModel;
        $existsValid = $certClass->newQuery()
            ->where('course_id', $course->id)
            ->where('user_id', $userId)
            ->where('created_at', '>=', $globalVersion)
            ->exists();

        if ($existsValid) {
            return false;
        }

        $cert = new $certificateModel([
            'course_id' => $course->id,
            'user_id'   => $userId,
        ]);
        $cert->created_at = now();
        $cert->updated_at = now();
        $cert->save();

        return true;
    }

    public function CompletedTest( Request $request, Chapter $chapter)
    {
        
    }
}
