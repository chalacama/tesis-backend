<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Chapter;
use App\Models\TypeQuestion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request, Chapter $chapter): JsonResponse
{
    // Autoriza contra el curso dueño del capítulo
    $course = $chapter->module?->course;
    $this->authorize('viewHidden', $course);

    // Parámetros de filtro/paginación
    $perPage   = (int) $request->input('per_page', 15);
    $search    = trim((string) $request->input('q', ''));
    $typeId    = $request->input('type_questions_id');
    $orderBy   = $request->input('order_by', 'spot'); // spot|created_at|id
    $orderDir  = strtolower($request->input('order_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    // ¿Incluir bandera de correctas?
    $includeCorrect = filter_var($request->input('include_correct', false), FILTER_VALIDATE_BOOLEAN);

    // Query base: preguntas con su tipo y respuestas
    $query = Question::query()
        ->with([
            'typeQuestion:id,nombre',
            'answers' => function ($q) use ($includeCorrect) {
                // Campos de respuesta (oculta is_correct si no se solicita)
                $cols = $includeCorrect
                    ? ['id', 'option', 'is_correct', 'question_id']
                    : ['id', 'option', 'question_id'];
                $q->select($cols)->orderBy('id', 'asc');
            },
        ])
        ->where('chapter_id', $chapter->id);

    // Filtros
    if ($search !== '') {
        $query->where('statement', 'like', "%{$search}%");
    }
    if (!empty($typeId)) {
        $query->where('type_questions_id', $typeId);
    }

    // Orden
    if (!in_array($orderBy, ['spot', 'created_at', 'id'], true)) {
        $orderBy = 'spot';
    }
    $query->orderBy($orderBy, $orderDir)->orderBy('id', 'asc');

    // Paginación
    $questions = $query->paginate($perPage)->appends($request->query());

    return response()->json([
        'filters' => [
            'q'                  => $search,
            'type_questions_id'  => $typeId,
            'order_by'           => $orderBy,
            'order_dir'          => $orderDir,
            'per_page'           => $perPage,
            'include_correct'    => $includeCorrect,
        ],
        'questions' => $questions->items(), // cada pregunta incluye typeQuestion y answers
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
        $this->authorize('update', $course); // Cambia a 'viewHidden' si realmente lo deseas así

        // Validación básica del payload
        $data = $request->validate([
            'questions' => ['required', 'array', 'min:1'],

            'questions.*.id' => ['nullable', 'integer', 'exists:questions,id'],
            'questions.*.statement' => ['required', 'string'],
            'questions.*.type_questions_id' => ['required', 'integer', 'exists:type_questions,id'],
            'questions.*.spot' => ['nullable', 'integer', 'min:1'],

            'questions.*.answers' => ['required', 'array', 'min:2'],
            'questions.*.answers.*.id' => ['nullable', 'integer', 'exists:answers,id'],
            'questions.*.answers.*.option' => ['required', 'string'],
            'questions.*.answers.*.is_correct' => ['required'], // bool/0/1/“true”/“false”
            // Si tu tabla answers tiene columna 'spot', habilita esta línea:
            // 'questions.*.answers.*.spot' => ['nullable', 'integer', 'min:1'],
        ]);

        $updated = DB::transaction(function () use ($chapter, $data) {

            // Preguntas actuales del capítulo (para validar pertenencia y detectar eliminadas)
            $currentQuestions = $chapter->questions()->with('answers')->get()->keyBy('id');

            // IDs enviados desde el front (los que deben quedar vivos)
            $submittedQIds = collect($data['questions'])
                ->pluck('id')->filter()->map(fn ($v) => (int) $v);

            // Soft-delete preguntas que ya no vienen en el payload
            $toDeleteQ = $currentQuestions->keys()->diff($submittedQIds);
            if ($toDeleteQ->isNotEmpty()) {
                Question::whereIn('id', $toDeleteQ)->delete();
            }

            $result = [];

            foreach ($data['questions'] as $qIndex => $q) {
                $qId = $q['id'] ?? null;

                // Asegura que la pregunta pertenezca a este capítulo
                if ($qId) {
                    $existing = $currentQuestions->get((int) $qId);
                    if (!$existing || (int) $existing->chapter_id !== (int) $chapter->id) {
                        abort(422, "La pregunta {$qId} no pertenece a este capítulo.");
                    }
                }

                $typeId = (int) $q['type_questions_id'];
                $isMultiple = in_array($typeId, [1, 2], true);

                // Normaliza y corrige flags de respuestas ANTES de guardar (para coherencia)
                $answersData = [];
                $firstCorrectIdx = -1;

                foreach ($q['answers'] as $aIdx => $a) {
                    $bool = filter_var($a['is_correct'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    // Acepta 0/1/"0"/"1"/true/false
                    if ($bool === null) {
                        $bool = (string) $a['is_correct'] === '1';
                    }

                    $answersData[] = [
                        'id' => $a['id'] ?? null,
                        'option' => $a['option'],
                        'is_correct' => $bool ? 1 : 0,
                        // 'spot' => $a['spot'] ?? ($aIdx + 1), // Habilita si tienes columna spot en answers
                    ];

                    if ($firstCorrectIdx === -1 && ($bool ? 1 : 0) === 1) {
                        $firstCorrectIdx = $aIdx;
                    }
                }

                // Si NO es múltiple, garantizar exactamente UNA correcta
                if (!$isMultiple) {
                    if ($firstCorrectIdx === -1) {
                        // Si ninguna marcada, forzamos la primera
                        $firstCorrectIdx = 0;
                    }
                    foreach ($answersData as $i => &$ad) {
                        $ad['is_correct'] = ($i === $firstCorrectIdx) ? 1 : 0;
                    }
                    unset($ad);
                }

                // Upsert de la pregunta
                $questionAttrs = [
                    'statement' => $q['statement'],
                    'type_questions_id' => $typeId,
                    'spot' => $q['spot'] ?? ($qIndex + 1),
                ];

                if ($qId) {
                    $question = $currentQuestions->get((int) $qId);
                    $question->update($questionAttrs);
                } else {
                    $question = $chapter->questions()->create($questionAttrs);
                }

                // Upsert de respuestas
                $currentAnswers = $question->answers()->get()->keyBy('id');
                $submittedAIds = collect($answersData)->pluck('id')->filter()->map(fn ($v) => (int) $v);

                // Eliminar respuestas que ya no están (duras, sin softDelete en Answer)
                $toDeleteA = $currentAnswers->keys()->diff($submittedAIds);
                if ($toDeleteA->isNotEmpty()) {
                    Answer::whereIn('id', $toDeleteA)->delete();
                }

                foreach ($answersData as $aIndex => $ad) {
                    $aId = $ad['id'] ?? null;

                    // Si viene id, validar que pertenezca a esta pregunta
                    if ($aId) {
                        $existingA = $currentAnswers->get((int) $aId);
                        if (!$existingA || (int) $existingA->question_id !== (int) $question->id) {
                            abort(422, "La respuesta {$aId} no pertenece a la pregunta {$question->id}.");
                        }
                        $existingA->option = $ad['option'];
                        $existingA->is_correct = $ad['is_correct'];
                        // Si tu tabla answers tiene 'spot', descomenta:
                        // $existingA->spot = $ad['spot'] ?? ($aIndex + 1);
                        $existingA->save();
                    } else {
                        $newA = $question->answers()->create([
                            'option' => $ad['option'],
                            'is_correct' => $ad['is_correct'],
                            // 'spot' => $ad['spot'] ?? ($aIndex + 1), // si existe la columna
                        ]);
                        // Si necesitas forzar 'spot' en un segundo save solo si existe la columna, puedes hacerlo aquí.
                    }
                }

                // Recolectar con respuestas frescas
                $result[] = $question->fresh(['answers']);
            }

            return $result;
        });

        return response()->json([
            'message' => 'Preguntas actualizadas correctamente.',
            'questions' => $updated,
        ], 200);
    }

}
