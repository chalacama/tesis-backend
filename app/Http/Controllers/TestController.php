<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Chapter;
use App\Models\Test;
use App\Models\TestView;
use App\Models\Question;
use App\Models\Answer;
use App\Models\UserAnswer;
use App\Models\TestViewQuestion;
use App\Models\TestViewAnswer;
use App\Models\User;
use App\Models\LikeChapter;
use App\Models\SavedCourse;

use Carbon\Carbon;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Muestra preguntas de un test para que el usuario las responda (paginado)
     * - Genera (o reusa) un TestView por intento.
     * - Persiste orden de preguntas y respuestas según config del Test.
     */
    
public function show(Request $request, Chapter $chapter)
{
    $this->authorize('viewChapter', $chapter);

    $user = $request->user();

    // Cargar relaciones necesarias
    $chapter->load(['module.course', 'test']);
    $course = optional($chapter->module)->course;
    $test   = $chapter->test;

    if (!$test) {
        return response()->json([
            'ok' => false,
            'message' => 'Este capítulo no tiene test configurado.',
        ], 404);
    }

    // ===== Preguntas: total y por intento (como en index) =====
    $questionsTotal = (int) $test->questions()->count();

    $random = (bool) ($test->random ?? false);
    $split  = (int)  ($test->split  ?? 1);
    $split  = max(1, min(2, $split)); // normaliza a 1 o 2

    $questionsPerAttempt = ($random && $split > 1)
        ? (int) ceil($questionsTotal / $split)
        : $questionsTotal;

    // ===== Intentos =====
    // Intento en progreso (no completado)
    $inProgress = TestView::where('test_id', $test->id)
        ->where('user_id', $user->id)
        ->whereNull('completed_at')
        ->latest('created_at')
        ->first();

    // Intentos completados
    $attemptsCompleted = (int) TestView::where('test_id', $test->id)
        ->where('user_id', $user->id)
        ->whereNotNull('completed_at')
        ->count();

    $limited = (int) ($test->limited ?? 0);

    // can_retry: si hay intento en progreso -> false; sino evalúa el límite
    $canRetry = $inProgress
        ? false
        : ($limited === 0 ? true : ($attemptsCompleted < $limited));

    // Último intento completado (solo si NO hay en progreso)
    $lastCompleted = $inProgress ? null : TestView::where('test_id', $test->id)
        ->where('user_id', $user->id)
        ->whereNotNull('completed_at')
        ->latest('completed_at')
        ->first();

    // Puede ver respuestas del último intento completado (si existe y test.incorrect==true)
    $canViewLastAnswers = !$inProgress && (bool)$test->incorrect && (bool)$lastCompleted;

    // ===== Score y completed_at del último TestView =====
    $lastCompletedAtIso = $lastCompleted
        ? Carbon::parse($lastCompleted->completed_at)->toIso8601String()
        : null;

    // Respetar configuración de visibilidad de score
    $lastCompletedScore = $lastCompleted
        ? ((bool)$test->score ? (float) $lastCompleted->score : null)
        : null;

    // ===== UserState y likes =====
    $userState = [
        'is_saved'      => $course
            ? SavedCourse::where('course_id', $course->id)->where('user_id', $user->id)->exists()
            : false,
        'liked_chapter' => LikeChapter::where('chapter_id', $chapter->id)->where('user_id', $user->id)->exists(),
    ];

    $likesTotal = (int) LikeChapter::where('chapter_id', $chapter->id)->count();

    return response()->json([
        'ok'   => true,
        'data' => [
            'course_title'  => $course?->title,
            'chapter_title' => $chapter->title,

            'test' => [
                'id'               => $test->id,
                'limited'          => $limited,
                'questions_count'  => $questionsPerAttempt, // ya aplicado split si corresponde
            ],

            'attempts' => [
                'can_retry'                 => $canRetry,
                'in_progress_test_view_id'  => $inProgress?->id ?? null,
                'completed'                 => $attemptsCompleted,
            ],

            // Metadata del último intento COMPLETADO (si no hay intento en progreso)
            'last_completed_test_view_id'   => $lastCompleted?->id,
            'last_completed_completed_at'   => $lastCompletedAtIso,   // ISO 8601 o null
            'last_completed_score'          => $lastCompletedScore,   // null si test->score == false

            'can_view_last_answers'         => (bool) $canViewLastAnswers,

            'user_state' => $userState,
            'likes_total'=> $likesTotal,
        ],
    ]);
}

    public function index(Request $request, Chapter $chapter)
{
    $this->authorize('viewChapter', $chapter);

    $request->validate([
        'page'         => ['nullable', 'integer', 'min:1'],
        'per_page'     => ['nullable', 'integer', 'min:1', 'max:50'],
        'review_last'  => ['nullable', 'boolean'], // ⬅️ modo revisión del último intento
    ]);

    $user    = $request->user();
    $perPage = (int) $request->input('per_page', 5);
    $page    = (int) $request->input('page', 1);
    $review  = (bool) $request->boolean('review_last', false);

    $chapter->load(['module.course', 'test']);
    $test = $chapter->test;

    if (!$test) {
        return response()->json([
            'ok' => false,
            'message' => 'Este capítulo no tiene test configurado.',
        ], 404);
    }

    // ===== Resolver intento a mostrar según modo =====
    $inProgress = TestView::where('test_id', $test->id)
        ->where('user_id', $user->id)
        ->whereNull('completed_at')
        ->latest('created_at')
        ->first();

    if ($review) {
        // No se puede revisar si hay intento en progreso
        if ($inProgress) {
            return response()->json([
                'ok' => false,
                'message' => 'Tienes un intento en progreso. No puedes revisar respuestas todavía.',
                'meta' => [
                    'in_progress_test_view_id' => $inProgress->id,
                ],
            ], 403);
        }

        // Debe permitir revisar y existir un intento completado
        if (!(bool)$test->incorrect) {
            return response()->json([
                'ok' => false,
                'message' => 'Este test no permite revisar respuestas.',
            ], 403);
        }

        $testView = TestView::where('test_id', $test->id)
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->first();

        if (!$testView) {
            return response()->json([
                'ok' => false,
                'message' => 'No tienes intentos completados para revisar.',
            ], 404);
        }
    } else {
        // Modo “resolver”: usa en progreso o crea uno nuevo (respetando limited)
        if ($inProgress) {
            $testView = $inProgress;
        } else {
            $limited = (int) ($test->limited ?? 0);
            $attemptsUsed = TestView::where('test_id', $test->id)
                ->where('user_id', $user->id)
                ->count();
            $attemptsLeft = $limited === 0 ? null : max(0, $limited - $attemptsUsed);

            if ($limited > 0 && $attemptsLeft <= 0) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Has alcanzado el límite de intentos para este test.',
                    'meta' => [
                        'limited'       => $limited,
                        'attempts_used' => $attemptsUsed,
                        'attempts_left' => 0,
                    ],
                ], 403);
            }
            $testView = $this->createTestViewWithOrder($user->id, $test);
        }
    }

    // ===== Paginación de preguntas del TestView =====
    $paginator = TestViewQuestion::with([
            'question' => function ($q) {
                $q->with([
                    'typeQuestion:id,nombre',
                    'answers' => function ($qa) {
                        // No exponemos is_correct aquí
                        $qa->select('id', 'question_id', 'option', 'order');
                    },
                ]);
            },
            'testViewAnswers.answer' => function ($qa) {
                $qa->select('id', 'option');
            }
        ])
        ->where('test_view_id', $testView->id)
        ->orderBy('order')
        ->paginate($perPage, ['*'], 'page', $page);

    // Preguntas de esta página
    $questionIdsOnPage = $paginator->getCollection()->pluck('question_id')->all();

    // Selecciones del usuario para estas preguntas.
    // ⬇️ En review incluimos spot/correct_spot para construir el score por pregunta.
    $selectedRows = UserAnswer::where('user_id', $user->id)
        ->where('test_view_id', $testView->id)
        ->whereIn('question_id', $questionIdsOnPage)
        ->when($review, fn($q) => $q->addSelect(['question_id','answer_id','spot','correct_spot']))
        ->when(!$review, fn($q) => $q->addSelect(['question_id','answer_id']))
        ->get();

    // [question_id => [answer_id, ...]]
    $selectedByQuestion = [];
    // Scoring por pregunta (primer row sirve, ya que guardamos mismo spot/correct_spot por pregunta)
    $qSpot         = [];
    $qCorrectSpot  = [];

    foreach ($selectedRows as $row) {
        $selectedByQuestion[$row->question_id][] = $row->answer_id;

        if ($review && !array_key_exists($row->question_id, $qSpot)) {
            // Si existen, tomar los del primer row de esa pregunta
            $qSpot[$row->question_id]        = $row->spot ?? null;
            $qCorrectSpot[$row->question_id] = $row->correct_spot ?? null;
        }
    }

    // Para calcular aciertos SOLO en modo revisión
    $answersMetaByQuestion = [];
    if ($review) {
        $answersMeta = Answer::whereIn('question_id', $questionIdsOnPage)
            ->get(['id','question_id','is_correct']);
        foreach ($answersMeta as $a) {
            $answersMetaByQuestion[$a->question_id]['correct'][]   = $a->is_correct ? $a->id : null;
            $answersMetaByQuestion[$a->question_id]['incorrect'][] = !$a->is_correct ? $a->id : null;
        }
        foreach ($answersMetaByQuestion as $qid => $sets) {
            $answersMetaByQuestion[$qid]['correct']   = array_values(array_filter($sets['correct']   ?? []));
            $answersMetaByQuestion[$qid]['incorrect'] = array_values(array_filter($sets['incorrect'] ?? []));
        }
    }

    // Construcción del payload de preguntas
    $questionsPayload = $paginator->getCollection()->map(function (TestViewQuestion $tvq) use ($selectedByQuestion, $answersMetaByQuestion, $review, $qSpot, $qCorrectSpot, $test) {
        $q = $tvq->question;

        $prompt = $q->statement ?? $q->title ?? $q->text ?? null;

        $typeId   = $q->typeQuestion->id     ?? null;
        $typeName = $q->typeQuestion->nombre ?? null;
        $typeKey  = null;
        if ($typeName) {
            $n = mb_strtolower($typeName);
            if (str_contains($n, 'opción') || str_contains($n, 'opcion')) {
                $typeKey = 'single';
            } elseif (str_contains($n, 'casilla')) {
                $typeKey = 'multiple';
            }
        }

        $selectedForThisQ = collect($selectedByQuestion[$q->id] ?? []);

        // is_correct_question (solo en review)
        $isCorrectQuestion = null;
        $selected_is_correct_map = [];

        if ($review) {
            $correctIds   = collect($answersMetaByQuestion[$q->id]['correct']   ?? []);
            $incorrectIds = collect($answersMetaByQuestion[$q->id]['incorrect'] ?? []);

            $selCorrect = $selectedForThisQ->intersect($correctIds)->values();
            $selWrong   = $selectedForThisQ->intersect($incorrectIds)->values();

            $isCorrectQuestion =
                ($selWrong->count() === 0) &&
                ($correctIds->count() > 0) &&
                ($selectedForThisQ->count() === $correctIds->count()) &&
                ($selCorrect->count() === $correctIds->count());

            // Mapa para selected_is_correct por respuesta seleccionada
            foreach ($selectedForThisQ as $aid) {
                $selected_is_correct_map[$aid] = $correctIds->contains($aid);
            }
        }

        $answers = $tvq->testViewAnswers->map(function (TestViewAnswer $tva) use ($selectedForThisQ, $selected_is_correct_map, $review) {
            $a = $tva->answer;
            $selected = $selectedForThisQ->contains($a->id);
            return [
                'id'                   => $a->id,
                'label'                => $a->option ?? null,
                'selected'             => $selected,
                // Solo revelar corrección si está en modo revisión y fue seleccionada
                'selected_is_correct'  => ($review && $selected)
                    ? (bool) ($selected_is_correct_map[$a->id] ?? false)
                    : null,
            ];
        })->values();

        // Spot/correct_spot por pregunta (solo si test permite score e incorrect)
        $showScorePerQuestion = $review && (bool)$test->incorrect && (bool)$test->score;

        return [
            'question_id'         => $q->id,
            'order'               => $tvq->order,
            'prompt'              => $prompt,
            'type'                => [
                'id'   => $typeId,
                'name' => $typeName,
                'key'  => $typeKey,
            ],
            'is_correct_question' => $isCorrectQuestion, // null si no es review
            'spot'                => $showScorePerQuestion ? ($qSpot[$q->id]        ?? 0.0) : null,
            'correct_spot'        => $showScorePerQuestion ? ($qCorrectSpot[$q->id] ?? 0.0) : null,
            'answers'             => $answers,
        ];
    })->values();

    return response()->json([
        'ok' => true,
        'context' => [
            'test_view_id' => $testView->id, // mantenemos solo esto
        ],
        'data' => [
            'questions' => $questionsPayload,
        ],
        'pagination' => [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'from'         => $paginator->firstItem(),
            'to'           => $paginator->lastItem(),
        ],
    ]);
}






/**
 * Crea un TestView y persiste el orden de preguntas y respuestas según la configuración.
 * - random=true → baraja preguntas y respuestas.
 * - split=2 (solo con random=true) → toma aprox. la mitad (ceil(total/2)).
 */
private function createTestViewWithOrder(int $userId, Test $test): TestView
{
    return DB::transaction(function () use ($userId, $test) {
        /** @var TestView $testView */
        $testView = TestView::create([
            'user_id'      => $userId,
            'test_id'      => $test->id,
            'completed_at' => null,
        ]);

        // Cargar preguntas + respuestas (usa 'option' en Answer)
        $questionsQuery = $test->questions()->with([
            'answers' => function ($a) {
                $a->select('id', 'question_id', 'option', 'order', 'is_correct');
            },
        ]);

        $random = (bool) ($test->random ?? false);
        $split  = (int) ($test->split ?? 1);
        $split  = max(1, min(2, $split)); // 1 o 2

        if ($random) {
            $questions = $questionsQuery->inRandomOrder()->get();
            if ($split > 1 && $questions->count() > 0) {
                $take = (int) ceil($questions->count() / $split);
                $questions = $questions->take($take)->values();
            }
        } else {
            $questions = $questionsQuery
                ->orderBy('order')
                ->orderBy('id')
                ->get();
        }

        // Persistir orden de preguntas y respuestas
        $qOrder = 1;
        foreach ($questions as $question) {
            $tvq = TestViewQuestion::create([
                'test_view_id' => $testView->id,
                'question_id'  => $question->id,
                'order'        => $qOrder++,
            ]);

            $answers = $question->answers ?? collect();

            if ($random) {
                $answers = $answers->shuffle()->values();
            } else {
                $answers = $answers->sortBy([
                    ['order', 'asc'],
                    ['id', 'asc'],
                ])->values();
            }

            $aOrder = 1;
            foreach ($answers as $answer) {
                TestViewAnswer::create([
                    'test_view_question_id' => $tvq->id,
                    'answer_id'             => $answer->id,
                    'order'                 => $aOrder++,
                ]);
            }
        }

        return $testView;
    });
}


    public function update(Request $request, TestView $testView)
{
    // Capítulo del test para autorización de visibilidad
    $chapter = $testView->test->chapter;
    $this->authorize('viewChapter', $chapter);

    // El intento debe pertenecer al usuario autenticado
    $user = $request->user();
    if ((int)$testView->user_id !== (int)$user->id) {
        return response()->json([
            'ok' => false,
            'message' => 'No puedes modificar un intento que no es tuyo.',
        ], 403);
    }

    // No se puede editar un intento ya completado
    if (!is_null($testView->completed_at)) {
        return response()->json([
            'ok' => false,
            'message' => 'Este intento ya fue completado. No es posible guardar cambios.',
        ], 409);
    }

    // (Regla limited): los intentos afectan la creación, no el autosave.
    // Aquí solo verificamos coherencia (defensivo: nunca debería bloquear si el intento existe).
    $test = $testView->test;
    $limited = (int)($test->limited ?? 0);
    if ($limited > 0) {
        $attemptsUsed = TestView::where('test_id', $test->id)
            ->where('user_id', $user->id)
            ->count();
        if ($attemptsUsed > $limited) {
            return response()->json([
                'ok' => false,
                'message' => 'Límite de intentos excedido para este test.',
            ], 403);
        }
    }

    // Validación de payload
    $validated = $request->validate([
        'question_id'        => ['required', 'integer', 'exists:questions,id'],
        'answer_ids'         => ['nullable', 'array'],
        'answer_ids.*'       => ['integer'],
        // opcional: para futuros modos, por ahora solo replace
        'mode'               => ['nullable', 'in:replace'],
    ]);

    $questionId = (int)$validated['question_id'];
    $answerIds  = collect($validated['answer_ids'] ?? [])->map(fn ($v) => (int)$v)->unique()->values();

    // La pregunta debe existir dentro del conjunto generado para este TestView
    $tvq = TestViewQuestion::where('test_view_id', $testView->id)
        ->where('question_id', $questionId)
        ->first();

    if (!$tvq) {
        return response()->json([
            'ok' => false,
            'message' => 'La pregunta indicada no pertenece a este intento.',
        ], 422);
    }

    // Solo se pueden guardar alternativas que fueron presentadas en este intento (ordenadas en TestViewAnswer)
    $allowedAnswerIds = TestViewAnswer::where('test_view_question_id', $tvq->id)
        ->pluck('answer_id')
        ->all();

    // Filtrar a las permitidas
    $selected = $answerIds->filter(fn ($id) => in_array($id, $allowedAnswerIds, true))->values();

    // Autosave atómico (modo replace)
    DB::transaction(function () use ($user, $testView, $questionId, $selected) {
        // Borrar selección anterior del usuario para esa pregunta en este intento
        UserAnswer::where('user_id', $user->id)
            ->where('test_view_id', $testView->id)
            ->where('question_id', $questionId)
            ->delete();

        // Insertar nueva selección (spot = orden recibido)
        $spot = 1;
        foreach ($selected as $answerId) {
            UserAnswer::create([
                'answer_id'    => $answerId,
                'user_id'      => $user->id,
                'test_view_id' => $testView->id,
                'question_id'  => $questionId,
                'is_correct'   => null,     // nunca evaluar aquí
                'spot'         => null,  // conserva el orden de selección enviado
            ]);
        }
    });

    // Respuesta amigable para el front (sin revelar correctas)
    // Devolvemos lo que quedó persistido
    $current = UserAnswer::where('user_id', $user->id)
        ->where('test_view_id', $testView->id)
        ->where('question_id', $questionId)
        // ->orderBy('spot')
        ->pluck('answer_id')
        ->values();

    // Meta de intentos (por si el UI quiere mostrarlo)
    $attemptsUsed = TestView::where('test_id', $test->id)->where('user_id', $user->id)->count();
    $attemptsLeft = $limited === 0 ? null : max(0, $limited - $attemptsUsed);

    return response()->json([
        'ok' => true,
        'message' => 'Guardado automático realizado.',
        'data' => [
            'test_view_id' => $testView->id,
            'question_id'  => $questionId,
            'answer_ids'   => $current,   // lo que quedó guardado
            'autosaved_at' => now()->toISOString(),
        ],
        'meta' => [
            'limited'        => $limited,
            'attempts_used'  => $attemptsUsed,
            'attempts_left'  => $attemptsLeft, // null = ilimitado
        ],
    ], 200);
}
}
