<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Course;
use App\Models\ContentView;
use App\Models\TestView;
use App\Models\Registration;
use App\Models\RatingCourse;
use App\Models\Category;
use Carbon\Carbon;

use App\Models\Module;
use App\Models\LearningContent;
use App\Models\Certificate;
use App\Models\Comment;
use App\Models\TestViewQuestion;
use App\Models\Question;
use App\Models\Answer;
use App\Models\UserAnswer;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class PanelController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        // 📅 Filtros opcionales de fecha
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $hasDateFilter = $dateFrom && $dateTo;
        $start = null;
        $end   = null;

        if ($hasDateFilter) {
            try {
                $start = Carbon::parse($dateFrom)->startOfDay();
                $end   = Carbon::parse($dateTo)->endOfDay();
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Formato de fechas inválido. Usa YYYY-MM-DD para date_from y date_to.'
                ], 422);
            }
        }

        // 🔐 Alcance: admin ve todo, tutor solo cursos donde ES DUEÑO
        $isAdmin = $user->hasRole('admin');

        if ($isAdmin) {
            $courseIds = Course::pluck('id');
        } else {
            $courseIds = Course::whereHas('owner', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->pluck('id');
        }

        // Validación rápida si no hay cursos
        if ($courseIds->isEmpty()) {
            return response()->json([
                'scope'         => $isAdmin ? 'admin' : 'tutor',
                'courses_count' => 0,
                'metrics'       => [
                    'content_completion' => [
                        'total_views'     => 0,
                        'completed'       => 0,
                        'in_progress'     => 0,
                        'completion_rate' => 0
                    ],
                    'test_performance'   => [],
                    'course_popularity'  => [],
                    'course_ratings'     => [
                        'distribution'  => [],
                        'average'       => 0,
                        'total_ratings' => 0
                    ],
                    'category_interests' => [],
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 1) Tasa de Finalización de Contenidos (Engagement)
        | Tabla: content_views (filtramos por updated_at, que refleja el progreso)
        |--------------------------------------------------------------------------
        */
        $contentQuery = ContentView::query()
            ->join('learning_contents', 'content_views.learning_content_id', '=', 'learning_contents.id')
            ->join('chapters', 'learning_contents.chapter_id', '=', 'chapters.id')
            ->join('modules', 'chapters.module_id', '=', 'modules.id')
            ->join('courses', 'modules.course_id', '=', 'courses.id')
            ->whereIn('courses.id', $courseIds);

        if ($hasDateFilter) {
            $contentQuery->whereBetween('content_views.updated_at', [$start, $end]);
        }

        $totalViews = (clone $contentQuery)->count();
        $completed  = (clone $contentQuery)
            ->where('content_views.progress', 100)
            ->count();

        $contentCompletion = [
            'total_views'     => $totalViews,
            'completed'       => $completed,
            'in_progress'     => max($totalViews - $completed, 0),
            'completion_rate' => $totalViews > 0 ? round(($completed / $totalViews) * 100, 2) : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | 2) Rendimiento Académico por Evaluación
        | Tablas: test_views + tests + chapters
        | Filtro: test_views.created_at entre fechas (fecha del intento)
        |--------------------------------------------------------------------------
        */
        $testPerformanceQuery = TestView::query()
            ->join('tests', 'test_views.test_id', '=', 'tests.id')
            ->join('chapters', 'tests.chapter_id', '=', 'chapters.id')
            ->join('modules', 'chapters.module_id', '=', 'modules.id')
            ->join('courses', 'modules.course_id', '=', 'courses.id')
            ->whereIn('courses.id', $courseIds);

        if ($hasDateFilter) {
            $testPerformanceQuery->whereBetween('test_views.created_at', [$start, $end]);
        }

        $testPerformance = $testPerformanceQuery
            ->select(
                'tests.id',
                'chapters.title',
                DB::raw('AVG(test_views.score) as avg_score'),
                DB::raw('COUNT(test_views.id) as attempts')
            )
            ->groupBy('tests.id', 'chapters.title')
            ->orderBy('chapters.title')
            ->get()
            ->map(function ($row) {
                return [
                    'label'     => $row->title,
                    'avg_score' => round((float) $row->avg_score, 2),
                    'attempts'  => (int) $row->attempts,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 3) Popularidad de Cursos (Inscripciones)
        | Tabla: registrations
        | Filtro:
        |   - con fecha: whereBetween(registrations.created_at)
        |   - sin fecha: últimos 6 meses
        |--------------------------------------------------------------------------
        */
        $registrationsQuery = Registration::query()
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->whereIn('courses.id', $courseIds);

        if ($hasDateFilter) {
            $registrationsQuery->whereBetween('registrations.created_at', [$start, $end]);
        } else {
            $registrationsQuery->where('registrations.created_at', '>=', now()->subMonths(6));
        }

        $registrationsByMonth = $registrationsQuery
            ->select(
                DB::raw("DATE_FORMAT(registrations.created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4) Satisfacción del Usuario (Ratings)
        | No filtramos por fecha (visión global de calidad del curso)
        |--------------------------------------------------------------------------
        */
        $rawRatings = RatingCourse::query()
            ->join('courses', 'rating_courses.course_id', '=', 'courses.id')
            ->whereIn('courses.id', $courseIds)
            ->select('rating_courses.stars', DB::raw('COUNT(*) as total'))
            ->groupBy('rating_courses.stars')
            ->get();

        $distribution = array_fill(1, 5, 0);
        $totalRatings = 0;
        $sumRatings   = 0;

        foreach ($rawRatings as $row) {
            $star = (int) $row->stars;
            if ($star >= 1 && $star <= 5) {
                $distribution[$star] = (int) $row->total;
                $totalRatings       += (int) $row->total;
                $sumRatings         += $star * (int) $row->total;
            }
        }

        $courseRatings = [
            'distribution'  => collect($distribution)->map(
                fn($t, $s) => ['stars' => (int)$s, 'total' => (int)$t]
            )->values(),
            'average'       => $totalRatings > 0 ? round($sumRatings / $totalRatings, 2) : 0,
            'total_ratings' => $totalRatings,
        ];

        /*
        |--------------------------------------------------------------------------
        | 5) Intereses por Categoría (TOP 5)
        | No filtrado por fecha: es un mapa general de intereses
        |--------------------------------------------------------------------------
        */
        if ($isAdmin) {
            $categoryIds = Category::has('userCategoryInterests')->pluck('id');
        } else {
            $categoryIds = Category::whereHas('courses', function ($q) use ($courseIds) {
                $q->whereIn('courses.id', $courseIds);
            })->pluck('id');
        }

        $categoryInterests = [];

        if ($categoryIds->isNotEmpty()) {
            $categoryInterests = Category::query()
                ->join('user_category_interests', 'categories.id', '=', 'user_category_interests.category_id')
                ->whereIn('categories.id', $categoryIds)
                ->select(
                    'categories.id',
                    'categories.name',
                    DB::raw('COUNT(user_category_interests.id) as total')
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderBy('total', 'desc')
                ->take(5)
                ->get()
                ->map(function ($row) {
                    return [
                        'category_id' => (int) $row->id,
                        'name'        => $row->name,
                        'total'       => (int) $row->total,
                    ];
                });
        }

        return response()->json([
            'scope'         => $isAdmin ? 'admin' : 'tutor',
            'courses_count' => $courseIds->count(),
            'metrics'       => [
                'content_completion' => $contentCompletion,
                'test_performance'   => $testPerformance,
                'course_popularity'  => $registrationsByMonth,
                'course_ratings'     => $courseRatings,
                'category_interests' => $categoryInterests,
            ],
        ]);
    }

    public function show(Request $request, Course $course): JsonResponse
{
    // Solo admin o dueño del curso
    $this->authorize('update', $course);

    // 📅 Filtros opcionales de fecha
    $dateFrom = $request->query('date_from');
    $dateTo   = $request->query('date_to');

    $hasDateFilter = $dateFrom && $dateTo;
    $start = null;
    $end   = null;

    if ($hasDateFilter) {
        try {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end   = Carbon::parse($dateTo)->endOfDay();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Formato de fechas inválido. Usa YYYY-MM-DD para date_from y date_to.'
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | A) Embudo de Retención por Módulos
    | - Módulos del curso
    | - Cuántos alumnos distintos han completado al menos 1 contenido del módulo
    |--------------------------------------------------------------------------
    */

    // Todos los módulos del curso (aunque tengan 0 completados)
    $modules = Module::where('course_id', $course->id)
        ->orderBy('order')
        ->get(['id', 'name', 'order']);

    // Conteo de alumnos que completaron al menos un contenido por módulo
    $completionsPerModule = ContentView::query()
        ->join('learning_contents', 'content_views.learning_content_id', '=', 'learning_contents.id')
        ->join('chapters', 'learning_contents.chapter_id', '=', 'chapters.id')
        ->join('modules', 'chapters.module_id', '=', 'modules.id')
        ->where('modules.course_id', $course->id)
        ->where(function ($q) {
            $q->where('content_views.progress', 100)
              ->orWhereNotNull('content_views.completed_at');
        })
        ->when($hasDateFilter, function ($q) use ($start, $end) {
            $q->whereBetween('content_views.completed_at', [$start, $end]);
        })
        ->select(
            'modules.id as module_id',
            DB::raw('COUNT(DISTINCT content_views.user_id) as students_completed')
        )
        ->groupBy('modules.id')
        ->pluck('students_completed', 'module_id'); // Collection: [module_id => students_completed]

    $retentionFunnel = $modules->map(function ($module) use ($completionsPerModule) {
        return [
            'module_id'          => (int) $module->id,
            'name'               => $module->name,
            'students_completed' => (int) ($completionsPerModule->get($module->id, 0)),
        ];
    })->values();

    /*
    |--------------------------------------------------------------------------
    | B) Tasa de Certificación
    | - registrations del curso (filtrado por created_at)
    | - certificates del curso (filtrado por created_at)
    |--------------------------------------------------------------------------
    */
    $registrationsQuery = Registration::where('course_id', $course->id);

    if ($hasDateFilter) {
        $registrationsQuery->whereBetween('created_at', [$start, $end]);
    }

    $totalRegistrations = $registrationsQuery->count();

    $certificatesQuery = Certificate::whereHas('registration', function ($q) use ($course) {
        $q->where('course_id', $course->id);
    });

    if ($hasDateFilter) {
        $certificatesQuery->whereBetween('created_at', [$start, $end]);
    }

    $totalCertificates = $certificatesQuery->count();

    $certification = [
        'registrations' => $totalRegistrations,
        'certificates'  => $totalCertificates,
        'rate'          => $totalRegistrations > 0
            ? round(($totalCertificates / $totalRegistrations) * 100, 2)
            : 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | C) Distribución de Progreso por alumno
    | - Solo alumnos inscritos (filtrados por fecha de registro)
    | - Progreso real: contenidos completados / total contenidos del curso
    |--------------------------------------------------------------------------
    */

    // Total de contenidos (learning_contents) del curso
    $totalContents = LearningContent::query()
        ->join('chapters', 'learning_contents.chapter_id', '=', 'chapters.id')
        ->join('modules', 'chapters.module_id', '=', 'modules.id')
        ->where('modules.course_id', $course->id)
        ->count('learning_contents.id');

    // Usuarios inscritos (aplica fecha solo aquí)
    $registrationsForProgress = Registration::where('course_id', $course->id)
        ->when($hasDateFilter, function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })
        ->get(['user_id']);

    $registeredUserIds = $registrationsForProgress->pluck('user_id')->unique();
    $totalStudents = $registeredUserIds->count();

    // Conteo de contenidos completados por usuario
    $completedByUser = collect();

    if ($totalContents > 0 && $totalStudents > 0) {
        $completedByUser = ContentView::query()
            ->join('learning_contents', 'content_views.learning_content_id', '=', 'learning_contents.id')
            ->join('chapters', 'learning_contents.chapter_id', '=', 'chapters.id')
            ->join('modules', 'chapters.module_id', '=', 'modules.id')
            ->where('modules.course_id', $course->id)
            ->whereIn('content_views.user_id', $registeredUserIds)
            ->where(function ($q) {
                $q->where('content_views.progress', 100)
                  ->orWhereNotNull('content_views.completed_at');
            })
            // Nota: aquí NO filtramos por fecha de view, solo por registro
            ->select(
                'content_views.user_id',
                DB::raw('COUNT(DISTINCT content_views.learning_content_id) as completed_contents')
            )
            ->groupBy('content_views.user_id')
            ->pluck('completed_contents', 'user_id');
    }

    $buckets = [
        'not_started' => [
            'label' => '0% (Sin empezar)',
            'count' => 0,
        ],
        'starting' => [
            'label' => '1-50% (Iniciando)',
            'count' => 0,
        ],
        'advanced' => [
            'label' => '51-99% (Avanzado)',
            'count' => 0,
        ],
        'completed' => [
            'label' => '100% (Finalizado)',
            'count' => 0,
        ],
    ];

    if ($totalStudents > 0 && $totalContents > 0) {
        foreach ($registeredUserIds as $userId) {
            $completed = (int) ($completedByUser->get($userId, 0));
            $percent   = ($totalContents > 0)
                ? ($completed / $totalContents) * 100
                : 0;

            if ($completed === 0) {
                $buckets['not_started']['count']++;
            } elseif ($percent > 0 && $percent <= 50) {
                $buckets['starting']['count']++;
            } elseif ($percent > 50 && $percent < 100) {
                $buckets['advanced']['count']++;
            } else {
                // >= 100%
                $buckets['completed']['count']++;
            }
        }
    }

    // Calculamos porcentaje por bucket
    foreach ($buckets as $key => $bucket) {
        $buckets[$key]['percentage'] = $totalStudents > 0
            ? round(($bucket['count'] * 100) / $totalStudents, 2)
            : 0;
    }

    $progressDistribution = [
        'total_students'  => $totalStudents,
        'total_contents'  => $totalContents,
        'buckets'         => array_values($buckets),
    ];

    /*
|--------------------------------------------------------------------------
| D) Top 5 Preguntas Difíciles
| Usamos user_answers.is_correct = 0
| Filtrado por user_answers.created_at dentro del rango de fechas (si aplica)
|--------------------------------------------------------------------------
*/
$topDifficultQuestions = UserAnswer::query()
    ->join('questions', 'user_answers.question_id', '=', 'questions.id')
    ->join('tests', 'questions.test_id', '=', 'tests.id')
    ->join('chapters', 'tests.chapter_id', '=', 'chapters.id')
    ->join('modules', 'chapters.module_id', '=', 'modules.id')
    ->where('modules.course_id', $course->id)
    ->when($hasDateFilter, function ($q) use ($start, $end) {
        $q->whereBetween('user_answers.created_at', [$start, $end]);
    })
    ->where('user_answers.is_correct', 0) // respuestas falladas
    ->select(
        'questions.id as question_id',
        'questions.statement',
        DB::raw('COUNT(*) as wrong_count')
    )
    ->groupBy('questions.id', 'questions.statement')
    ->orderByDesc('wrong_count')
    ->take(5)
    ->get()
    ->map(function ($row) {
        return [
            'question_id'    => (int) $row->question_id,
            'statement'      => $row->statement,
            'wrong_attempts' => (int) $row->wrong_count,
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | E) Feedback Reciente
    | - Últimos 5 comentarios del curso
    | - Filtrados por created_at si hay rango de fechas
    | - Incluye nombre usuario + texto + estrellas + fecha
    |--------------------------------------------------------------------------
    */
    $feedbackQuery = Comment::query()
        ->with('user')
        ->where('commentable_type', Course::class)
        ->where('commentable_id', $course->id);

    if ($hasDateFilter) {
        $feedbackQuery->whereBetween('created_at', [$start, $end]);
    }

    $feedbackComments = $feedbackQuery
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // Obtenemos estrellas por usuario para este curso
    $userIds = $feedbackComments->pluck('user_id')->unique();

    $starsByUser = RatingCourse::where('course_id', $course->id)
        ->whereIn('user_id', $userIds)
        ->pluck('stars', 'user_id'); // [user_id => stars]

    $recentFeedback = $feedbackComments->map(function (Comment $comment) use ($starsByUser) {
        $user = $comment->user;
        return [
            'user_name'  => $user->name ?? ($user->username ?? $user->email ?? 'Usuario'),
            'text'       => $comment->texto,
            'stars'      => $starsByUser->has($comment->user_id)
                ? (int) $starsByUser->get($comment->user_id)
                : null,
            'created_at' => optional($comment->created_at)->toDateTimeString(),
        ];
    });

    return response()->json([
        'course' => [
            'id'    => $course->id,
            'title' => $course->title,
        ],
        'filters' => [
            'date_from' => $hasDateFilter ? $start->toDateString() : null,
            'date_to'   => $hasDateFilter ? $end->toDateString() : null,
        ],
        'metrics' => [
            'retention_funnel'      => $retentionFunnel,        // A
            'certification'         => $certification,          // B
            'progress_distribution' => $progressDistribution,   // C
            'top_difficult_questions' => $topDifficultQuestions,// D
            'recent_feedback'       => $recentFeedback,         // E
        ],
    ]);
}


}
