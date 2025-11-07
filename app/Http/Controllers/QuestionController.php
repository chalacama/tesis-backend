<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Chapter;
use App\Models\Test;
use App\Models\TypeQuestion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\QuestionResource;
class QuestionController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request, Chapter $chapter): JsonResponse
{
    // Autoriza contra el curso dueño del capítulo
    $course = $chapter->module?->course;
    $this->authorize('viewHidden', $course);

    // Parámetros
    $perPage   = (int) $request->input('per_page', 15);
    $search    = trim((string) $request->input('q', ''));
    $typeId    = $request->input('type_questions_id');
    $orderBy   = $request->input('order_by', 'order'); // order|spot|created_at|id
    $orderDir  = strtolower($request->input('order_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
    $includeCorrect = filter_var($request->input('include_correct', false), FILTER_VALIDATE_BOOLEAN);

    // Test del capítulo (puede no existir aún)
    $test = $chapter->test()->first();

    // Base query (si no hay test => devolver vacío)
    $query = Question::query()
        ->when($test, fn ($q) => $q->where('test_id', $test->id)->whereNull('deleted_at'),
            fn ($q) => $q->whereRaw('1=0')) // sin test => lista vacía
        ->with([
            'typeQuestion:id,nombre',
            'answers' => function ($q) use ($includeCorrect) {
                $cols = $includeCorrect
                    ? ['id', 'option', 'is_correct', 'order', 'question_id']
                    : ['id', 'option', 'order', 'question_id'];
                $q->select($cols)
                  ->orderBy('order', 'asc')
                  ->orderBy('id', 'asc');
            },
        ]);

    // Filtros
    if ($search !== '') {
        $query->where('statement', 'like', "%{$search}%");
    }
    if (!empty($typeId)) {
        $query->where('type_questions_id', $typeId);
    }

    // Orden (prioriza 'order')
    if (!in_array($orderBy, ['order', 'spot', 'created_at', 'id'], true)) {
        $orderBy = 'order';
    }
    $query->orderBy($orderBy, $orderDir)->orderBy('id', 'asc');

    // Paginación
    $questions = $query->paginate($perPage)->appends($request->query());

    // Meta de configuración del test
    $testConfig = $test ? [
        'id'         => $test->id,
        'chapter_id' => $chapter->id,
        'random'     => (bool) $test->random,
        'incorrect'  => (bool) $test->incorrect,
        'score'      => (bool) $test->score,
        'split'      => (int)  $test->split,
        'limited'    => (int) ($test->limited ?? 0),
        'questions_count' => (int) $test->questions()->count(),
        'updated_at' => optional($test->updated_at)->toISOString(),
        'created_at' => optional($test->created_at)->toISOString(),
    ] : null;

    return response()->json([
        'filters' => [
            'q'                  => $search,
            'type_questions_id'  => $typeId,
            'order_by'           => $orderBy,
            'order_dir'          => $orderDir,
            'per_page'           => $perPage,
            'include_correct'    => $includeCorrect,
        ],
        'test' => $testConfig,
        'questions' => $questions->items(),
        'meta' => [
            'current_page' => $questions->currentPage(),
            'per_page'     => $questions->perPage(),
            'total'        => $questions->total(),
            'last_page'    => $questions->lastPage(),
            'has_more'     => $questions->hasMorePages(),
        ],
    ]);
}
public function update(Request $request, Chapter $chapter): JsonResponse
{
    // Autoriza contra el curso dueño del capítulo
    $course = $chapter->module?->course;
    $this->authorize('update', $course);

    // Validación
    $data = $request->validate([
        // Config del test (opcional)
        'test' => ['sometimes', 'array'],
        'test.random'    => ['sometimes', 'boolean'],
        'test.incorrect' => ['sometimes', 'boolean'],
        'test.score'     => ['sometimes', 'boolean'],
        'test.split'     => ['sometimes', 'integer', 'min:1' , 'max:2'],
        'test.limited'   => ['sometimes', 'integer', 'min:0' , 'max:2'],

        // Preguntas
        'questions' => ['required', 'array', 'min:1'],

        'questions.*.id'               => ['nullable', 'integer', 'exists:questions,id'],
        'questions.*.statement'        => ['required', 'string'],
        'questions.*.type_questions_id'=> ['required', 'integer', 'exists:type_questions,id'],
        'questions.*.spot'             => ['nullable', 'integer', 'min:0'],
        'questions.*.order'            => ['nullable', 'integer', 'min:1'],

        'questions.*.answers'                  => ['required', 'array', 'min:2'],
        'questions.*.answers.*.id'             => ['nullable', 'integer', 'exists:answers,id'],
        'questions.*.answers.*.option'         => ['required', 'string'],
        'questions.*.answers.*.is_correct'     => ['required'], // bool-ish
        'questions.*.answers.*.order'          => ['nullable', 'integer', 'min:1'],
    ]);

    $result = DB::transaction(function () use ($chapter, $data) {

        // Asegura que exista un test para este capítulo (si no, créalo con defaults)
        $test = $chapter->test()->first();
        if (!$test) {
            $test = Test::create([
                'chapter_id' => $chapter->id,
                'random'     => $data['test']['random']    ?? true,
                'incorrect'  => $data['test']['incorrect'] ?? true,
                'score'      => $data['test']['score']     ?? false,
                'split'      => $data['test']['split']     ?? 1,
                'limited'    => $data['test']['limited']   ?? 0,
            ]);
        } elseif (!empty($data['test'])) {
            // Actualiza configuración del test si vino en el payload
            $test->fill([
                'random'    => array_key_exists('random',    $data['test']) ? (bool) $data['test']['random']    : $test->random,
                'incorrect' => array_key_exists('incorrect', $data['test']) ? (bool) $data['test']['incorrect'] : $test->incorrect,
                'score'     => array_key_exists('score',     $data['test']) ? (bool) $data['test']['score']     : $test->score,
                'split'     => array_key_exists('split',     $data['test']) ? (int)  $data['test']['split']     : $test->split,
                'limited'   => array_key_exists('limited',   $data['test']) ? (int) $data['test']['limited']   : ($test->limited ?? 0),
            ])->save();
        }

        // Preguntas actuales del test (para validar pertenencia y detectar eliminadas)
        $currentQuestions = $test->questions()->with('answers')->get()->keyBy('id');

        // IDs enviados desde el front (los que deben quedar vivos)
        $submittedQIds = collect($data['questions'])
            ->pluck('id')->filter()->map(fn ($v) => (int) $v);

        // Soft-delete preguntas que ya no vienen
        $toDeleteQ = $currentQuestions->keys()->diff($submittedQIds);
        if ($toDeleteQ->isNotEmpty()) {
            Question::whereIn('id', $toDeleteQ)->delete();
        }

        $out = [];

        foreach ($data['questions'] as $qIndex => $q) {
            $qId = $q['id'] ?? null;

            // Si viene id, debe pertenecer a este test
            if ($qId) {
                $existing = $currentQuestions->get((int) $qId);
                if (!$existing || (int) $existing->test_id !== (int) $test->id) {
                    abort(422, "La pregunta {$qId} no pertenece al test del capítulo.");
                }
            }

            $typeId     = (int) $q['type_questions_id'];
            $isMultiple = in_array($typeId, [1, 2], true); // ajusta según tus tipos

            // Normaliza respuestas y sus órdenes
            $answersData = [];
            $firstCorrectIdx = -1;

            foreach ($q['answers'] as $aIdx => $a) {
                $bool = filter_var($a['is_correct'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($bool === null) {
                    $bool = (string) $a['is_correct'] === '1';
                }
                $answersData[] = [
                    'id'         => $a['id'] ?? null,
                    'option'     => $a['option'],
                    'is_correct' => $bool ? 1 : 0,
                    'order'      => isset($a['order']) ? (int)$a['order'] : ($aIdx + 1),
                ];
                if ($firstCorrectIdx === -1 && ($bool ? 1 : 0) === 1) {
                    $firstCorrectIdx = $aIdx;
                }
            }

            // Si NO es múltiple, forzar exactamente una correcta
            if (!$isMultiple) {
                if ($firstCorrectIdx === -1) {
                    $firstCorrectIdx = 0;
                }
                foreach ($answersData as $i => &$ad) {
                    $ad['is_correct'] = ($i === $firstCorrectIdx) ? 1 : 0;
                }
                unset($ad);
            }

            // Upsert de la pregunta
            $questionAttrs = [
                'statement'          => $q['statement'],
                'type_questions_id'  => $typeId,
                'spot'               => isset($q['spot'])  ? (int)$q['spot']  : ($qIndex + 1),
                'order'              => isset($q['order']) ? (int)$q['order'] : ($qIndex + 1),
            ];

            if ($qId) {
                $question = $currentQuestions->get((int) $qId);
                $question->update($questionAttrs);
            } else {
                $question = $test->questions()->create($questionAttrs);
            }

            // Upsert de respuestas (ordenadas)
            $currentAnswers = $question->answers()->get()->keyBy('id');
            $submittedAIds  = collect($answersData)->pluck('id')->filter()->map(fn ($v) => (int) $v);

            // Eliminar respuestas que ya no están
            $toDeleteA = $currentAnswers->keys()->diff($submittedAIds);
            if ($toDeleteA->isNotEmpty()) {
                Answer::whereIn('id', $toDeleteA)->delete();
            }

            foreach ($answersData as $aIndex => $ad) {
                $aId = $ad['id'] ?? null;
                if ($aId) {
                    $existingA = $currentAnswers->get((int) $aId);
                    if (!$existingA || (int) $existingA->question_id !== (int) $question->id) {
                        abort(422, "La respuesta {$aId} no pertenece a la pregunta {$question->id}.");
                    }
                    $existingA->option     = $ad['option'];
                    $existingA->is_correct = $ad['is_correct'];
                    $existingA->order      = $ad['order'];
                    $existingA->save();
                } else {
                    $question->answers()->create([
                        'option'     => $ad['option'],
                        'is_correct' => $ad['is_correct'],
                        'order'      => $ad['order'],
                    ]);
                }
            }

            // Recarga con respuestas ordenadas
            $out[] = $question->fresh([
                'answers' => fn($q) => $q->orderBy('order', 'asc')->orderBy('id', 'asc')
            ]);
        }

        return [
            'test' => [
                'id'         => $test->id,
                'chapter_id' => $chapter->id,
                'random'     => (bool) $test->random,
                'incorrect'  => (bool) $test->incorrect,
                'score'      => (bool) $test->score,
                'split'      => (int)  $test->split,
                'limited'    => (int) ($test->limited ?? 0),
            ],
            'questions' => $out,
        ];
    });

    return response()->json([
        'message'   => 'Test y preguntas actualizados correctamente.',
        'test'      => $result['test'],
        'questions' => $result['questions'],
    ], 200);
}



}
