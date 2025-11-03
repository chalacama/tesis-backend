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


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Muestra preguntas de un test para que el usuario las responda (paginado)
     * - Genera (o reusa) un TestView por intento.
     * - Persiste orden de preguntas y respuestas según config del Test.
     */
    public function index(Request $request, Chapter $chapter)
{
    $this->authorize('viewChapter', $chapter);

    $request->validate([
        'page'     => ['nullable', 'integer', 'min:1'],
        'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
    ]);

    $user   = $request->user();
    $perPage = (int) $request->input('per_page', 5);
    $page    = (int) $request->input('page', 1);

    // Cargar contexto
    $chapter->load(['module.course', 'test']);
    $test = $chapter->test;

    if (!$test) {
        return response()->json([
            'ok' => false,
            'message' => 'Este capítulo no tiene test configurado.',
        ], 404);
    }

    // Intentos usados y límite
    $attemptsUsed = TestView::where('test_id', $test->id)
        ->where('user_id', $user->id)
        ->count();

    $limited = (int) ($test->limited ?? 0);
    $attemptsLeft = $limited === 0 ? null : max(0, $limited - $attemptsUsed);

    // Buscar el último TestView del usuario para este test
    $latestView = TestView::where('test_id', $test->id)
        ->where('user_id', $user->id)
        ->latest() // por created_at desc
        ->first();

    if ($latestView && is_null($latestView->completed_at)) {
        // Reutilizar intento en curso
        $testView = $latestView;
    } else {
        // Necesita crear uno nuevo (si el límite lo permite)
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
        // Recalcular intentos (opcional)
        $attemptsUsed += 1;
        $attemptsLeft = $limited === 0 ? null : max(0, $limited - $attemptsUsed);
    }

    // Preguntas en el orden guardado
    $paginator = TestViewQuestion::with([
            'question' => function ($q) {
                $q->with([
                    'typeQuestion:id,nombre',
                    'answers' => function ($qa) {
                        // Usa 'option' y NO expongas is_correct
                        $qa->select('id', 'question_id', 'option', 'order');
                    },
                ]);
            },
            'testViewAnswers.answer' => function ($qa) {
                $qa->select('id', 'option'); // sin is_correct
            }
        ])
        ->where('test_view_id', $testView->id)
        ->orderBy('order')
        ->paginate($perPage, ['*'], 'page', $page);

    // Armar payload sin revelar correctas
    $questionsPayload = $paginator->getCollection()->map(function (TestViewQuestion $tvq) {
        $q = $tvq->question;

        $prompt = $q->statement
            ?? $q->title
            ?? $q->text
            ?? null;

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

        $answers = $tvq->testViewAnswers->map(function (TestViewAnswer $tva) {
            $a = $tva->answer;
            return [
                'id'    => $a->id,
                'label' => $a->option ?? null,
            ];
        })->values();

        return [
            'question_id' => $q->id,
            'order'       => $tvq->order,
            'prompt'      => $prompt,
            'type'        => [
                'id'   => $typeId,
                'name' => $typeName,
                'key'  => $typeKey, // 'single' | 'multiple' | null
            ],
            'answers'     => $answers,
        ];
    })->values();

    $course = optional($chapter->module)->course;

    return response()->json([
        'ok' => true,
        'context' => [
            'course_title'  => $course?->title,
            'chapter_title' => $chapter->title,
            'test' => [
                'id'             => $test->id,
                'random'         => (bool) $test->random,
                'split'          => (int) ($test->split ?? 1),
                'limited'        => $limited,
                'attempts_used'  => $attemptsUsed,
                'attempts_left'  => $attemptsLeft,
            ],
            'test_view_id' => $testView->id,
            'test_title'   => 'Evaluación — ' . $chapter->title,
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
                'spot'         => $spot++,  // conserva el orden de selección enviado
            ]);
        }
    });

    // Respuesta amigable para el front (sin revelar correctas)
    // Devolvemos lo que quedó persistido
    $current = UserAnswer::where('user_id', $user->id)
        ->where('test_view_id', $testView->id)
        ->where('question_id', $questionId)
        ->orderBy('spot')
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
