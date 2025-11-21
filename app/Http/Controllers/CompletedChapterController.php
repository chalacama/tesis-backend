<?php

namespace App\Http\Controllers;

use App\Models\CompletedChapter;
use App\Models\LearningContent;
use App\Models\ContentView;
use App\Models\TestView;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\User;
use App\Models\Test;
use App\Models\Answer;
use App\Models\Question;

use App\Models\UserAnswer;
use App\Models\TestViewQuestion;
use App\Models\TestViewAnswer;
use App\Models\Registration;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Notifications\CertificateObtainedNotification;

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

        $this->authorize('viewChapter', $chapter);

        // 2) Verificar registro del usuario en el curso
        $userId = Auth::id();
        $isRegistered = Registration::query()
            ->where('course_id', $course->id)
            ->where('user_id', $userId)
            ->exists();

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

if (!$hasQuestions && $contentAt) {
    $test = $chapter->test; // puede existir un test "vacío" (0 preguntas)
    if ($test && !TestView::query()
            ->where('user_id', $userId)
            ->where('test_id', $test->id)
            ->exists()) {
        TestView::create([
            'user_id'      => $userId,
            'test_id'      => $test->id,
            'completed_at' => now(), // lo marcamos como completado automáticamente
        ]);
        $autoCompletedTest = true;
    }
}


            // 4.3) test_at (último intento)
            // 4.3) test_at (último intento)
            $testAt = TestView::query()
                ->where('user_id', $userId)
                ->whereHas('test', fn($q) => $q->where('chapter_id', $chapter->id))
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

    // content_at (fecha de completitud del contenido o, si no hay contenido, del test)
    if ($chapter->learningContent) {
        $contentAt = ContentView::query()
            ->where('user_id', $userId)
            ->where('learning_content_id', $chapter->learningContent->id)
            ->value('completed_at');
        $contentAt = $contentAt ? Carbon::parse($contentAt) : null;
    } else {
        // NO hay contenido; usamos completitud del test en este capítulo
        $contentAt = TestView::query()
            ->where('user_id', $userId)
            ->whereHas('test', fn($q) => $q->where('chapter_id', $chapter->id))
            ->value('completed_at');
        $contentAt = $contentAt ? Carbon::parse($contentAt) : null;
    }

    // test_at (fecha de completitud del test o, si no hay preguntas, del contenido)
    if ($chapter->questions()->exists()) {
        $testAt = TestView::query()
            ->where('user_id', $userId)
            ->whereHas('test', fn($q) => $q->where('chapter_id', $chapter->id))
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
    // private function chapterVersionAt(Chapter $chapter): ?Carbon
    // {
    //     $lcCreated = $chapter->learningContent?->created_at
    //         ? Carbon::parse($chapter->learningContent->created_at)
    //         : null;

    //     $lastQ = $chapter->questions()->max('created_at');
    //     $qCreated = $lastQ ? Carbon::parse($lastQ) : null;

    //     if ($lcCreated && $qCreated) {
    //         return $lcCreated->greaterThan($qCreated) ? $lcCreated : $qCreated;
    //     }
    //     return $lcCreated ?: $qCreated;
    // }
private function chapterVersionAt(Chapter $chapter): ?Carbon
{
    $lcCreated = $chapter->learningContent?->created_at
        ? Carbon::parse($chapter->learningContent->created_at)
        : null;

    // Calificar la columna para evitar ambigüedad:
    $qTable  = (new Question)->getTable(); // normalmente 'questions'
    $lastQ   = $chapter->questions()->max("$qTable.created_at");
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
    // Debe existir el modelo Certificate
    $certificateModel = 'App\\Models\\Certificate';
    if (!class_exists($certificateModel)) {
        return false;
    }

    // Debe existir registro del usuario en el curso
    $registration = Registration::query()
        ->where('course_id', $course->id)
        ->where('user_id', $userId)
        ->first();

    if (!$registration) {
        return false;
    }

    // Todos los capítulos del curso
    $chapters = Chapter::query()
        ->whereIn('module_id', $course->modules()->pluck('id'))
        ->get();

    if ($chapters->isEmpty()) {
        return false;
    }

    // Comprobar completitud válida por versión de capítulo
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
            return false; // aún no cumple todos los capítulos
        }
    }

    $globalVersion = $globalVersion ?: now()->subSecond();

    // ¿Ya existe un certificado vigente?
    $cert = (new $certificateModel);
    $existsValid = $cert->newQuery()
        ->where('registration_id', $registration->id)
        ->where('created_at', '>=', $globalVersion)
        ->exists();

    if ($existsValid) {
        return false;
    }

    // Calcular total_score (promedio de scores de TestView completados en el curso)
    $chapterIds = $chapters->pluck('id');

    $testIds = Test::query()
        ->whereIn('chapter_id', $chapterIds)
        ->pluck('id');

    $avgScore = TestView::query()
        ->where('user_id', $userId)
        ->whereIn('test_id', $testIds)
        ->whereNotNull('completed_at')
        ->avg('score'); // promedio sobre 10 (ignora null)

    $totalScore = $avgScore !== null ? round((float)$avgScore, 2) : 0.00;

    // Generar code único
    do {
        $code = Str::upper(Str::random(12));
        $dup = $cert->newQuery()->where('code', $code)->exists();
    } while ($dup);

    // Crear certificado
    $newCert = new $certificateModel([
        'registration_id' => $registration->id,
        'code'            => $code,
        'total_score'     => $totalScore,
    ]);
    $newCert->save();

    // 🔔 Notificar al usuario que obtuvo el certificado
    $user = User::find($userId);

    if ($user) {
        $user->notify(new CertificateObtainedNotification($newCert, $course));
    }

    return true;
}


    public function completedTest(TestView $testView)
{
    $userId = Auth::id();

    // 1) Relaciones y pertenencia
    $test = $testView->test;
    if (!$test) {
        return response()->json([
            'ok' => false,
            'message' => 'El TestView no está asociado a un Test válido. Asegúrate de finalizar un intento generado por el endpoint que crea TestView con test_id.',
        ], 422);
    }

    $chapter = $test->chapter;
    if (!$chapter) {
        return response()->json([
            'ok' => false,
            'message' => 'El Test no está asociado a un capítulo válido.',
        ], 422);
    }

    $this->authorize('viewChapter', $chapter);

    if ((int)$testView->user_id !== (int)$userId) {
        return response()->json([
            'ok' => false,
            'message' => 'No puedes completar un intento que no te pertenece.',
        ], 403);
    }

    // 2) Transacción principal
    $payload = DB::transaction(function () use ($testView, $test, $chapter, $userId) {

        // 2.1) Verificar que el intento tenga preguntas y todas estén respondidas
        $questionIds = TestViewQuestion::query()
            ->where('test_view_id', $testView->id)
            ->pluck('question_id')
            ->all();

        $totalQuestions = count($questionIds);
        if ($totalQuestions === 0) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'El intento no contiene preguntas (test_view_questions).',
            ];
        }

        $answeredByQ = UserAnswer::query()
            ->select('question_id', DB::raw('COUNT(*) as n'))
            ->where('test_view_id', $testView->id)
            ->groupBy('question_id')
            ->pluck('n', 'question_id')
            ->toArray();

        $unanswered = array_values(array_diff($questionIds, array_keys($answeredByQ)));
        if (!empty($unanswered)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Aún hay preguntas sin responder.',
                'data' => [
                    'total_questions' => $totalQuestions,
                    'answered'        => $totalQuestions - count($unanswered),
                    'unanswered_ids'  => $unanswered,
                ],
            ];
        }

        // 2.2) Calcular y GUARDAR scoring por pregunta + score total (sobre 10)
        $sumSpot = 0.0;
        $sumCorrectSpot = 0.0;

        foreach ($questionIds as $qid) {

            // Todas las opciones de la pregunta
            $answers = Answer::query()
                ->where('question_id', $qid)
                ->get(['id','is_correct']);

            $correctIds   = $answers->where('is_correct', 1)->pluck('id')->values()->all();
            $incorrectIds = $answers->where('is_correct', 0)->pluck('id')->values()->all();

            // Selecciones del usuario (solo las seleccionadas)
            $selectedIds = UserAnswer::query()
                ->where('test_view_id', $testView->id)
                ->where('question_id', $qid)
                ->pluck('answer_id')
                ->values()
                ->all();

            $selCorrect = array_values(array_intersect($selectedIds, $correctIds));
            $selWrong   = array_values(array_intersect($selectedIds, $incorrectIds));

            $totalCorrect = count($correctIds);

            // SPOT: valor máximo de la pregunta (= #correctas), 0 si no hay correctas definidas
            $spot = $totalCorrect > 0 ? (float)$totalCorrect : 0.0;

            // correct_spot
            if ($totalCorrect <= 1) {
                // opción única
                $correct_spot = (count($selCorrect) === 1 && count($selWrong) === 0) ? 1.0 : 0.0;
                if ($spot === 0.0) { $correct_spot = 0.0; }
            } else {
                // checkboxes: cada incorrecta resta 0.5
                $correct_spot = count($selCorrect) - 0.5 * count($selWrong);
                $correct_spot = max(0.0, min($correct_spot, $spot));
            }
            $correct_spot = round($correct_spot, 2);

            // Guardar spot/correct_spot por pregunta en TODOS los user_answers de esa pregunta
            UserAnswer::query()
                ->where('test_view_id', $testView->id)
                ->where('question_id', $qid)
                ->update([
                    'spot'         => $spot,
                    'correct_spot' => $correct_spot,
                ]);

            // Guardar is_correct por selección del usuario
            $ansIsCorrectMap = $answers->pluck('is_correct', 'id'); // [answer_id => 0/1]
            $uaRows = UserAnswer::query()
                ->where('test_view_id', $testView->id)
                ->where('question_id', $qid)
                ->get(['id','answer_id']);

            foreach ($uaRows as $ua) {
                $ua->is_correct = (bool) ($ansIsCorrectMap[$ua->answer_id] ?? false);
                $ua->save();
            }

            // Acumuladores (ignorar spot=0)
            if ($spot > 0) {
                $sumSpot        += $spot;
                $sumCorrectSpot += $correct_spot;
            }
        }

        // Score sobre 10 (ignorando preguntas con spot=0)
        $score10 = ($sumSpot > 0) ? round(($sumCorrectSpot / $sumSpot) * 10, 2) : null;

        // 2.3) Marcar intento como completado (idempotente) y guardar score
        $touchCompleted = false;
        if (is_null($testView->completed_at)) {
            $testView->completed_at = now();
            $touchCompleted = true;
        }
        $testView->score = $score10; // puede ser null
        $testView->save();

        $completedAt = Carbon::parse($testView->completed_at);

        // 2.4) Completar capítulo y emitir certificado
        $course             = $chapter->module->course;
        $chapterCompleted   = $this->ensureChapterCompletion($chapter, $userId);
        $certificateIssued  = $this->issueCertificateIfEligible($course, $userId);

        // 2.5) Límite de intentos
        $limit = (int)($test->limited ?? 0); // 0/NULL = ilimitado
        $attemptsCompleted = TestView::query()
            ->where('user_id', $userId)
            ->where('test_id', $test->id)
            ->whereNotNull('completed_at')
            ->count();
        $canRetry = ($limit <= 0) ? true : ($attemptsCompleted < $limit);

        // 2.6) Respuesta ligera (sin 'detail')
        $showScore = (bool) $test->score;

        return [
            'ok'      => true,
            'status'  => 200,
            'message' => $touchCompleted ? 'Test completado correctamente.' : 'El intento ya estaba completado.',
            'data'    => [
                'test_view_id'        => $testView->id,
                'test_id'             => $test->id,
                'chapter_id'          => $chapter->id,
                'completed_at'        => $completedAt->toIso8601String(),
                'total_questions'     => $totalQuestions,
                'attempts_completed'  => $attemptsCompleted,
                'limit'               => $limit > 0 ? $limit : null,
                'can_retry'           => $canRetry,
                'chapter_completed'   => $chapterCompleted,
                'certificate_issued'  => $certificateIssued,
                'score'               => $showScore ? $score10 : null,
            ],
        ];
    });

    $status = $payload['status'] ?? 200;
    unset($payload['status']);

    return response()->json($payload, $status);
}






}
