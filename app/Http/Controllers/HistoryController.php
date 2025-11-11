<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Course;
use App\Models\Module;
use App\Models\Chapter;
use App\Models\ContentView;
use App\Models\SavedCourse;
use App\Models\Certificate;
use App\Models\Registration;
use App\Models\MiniatureCourse;
use App\Models\TutorCourse;
use App\Models\CompletedChapter;

class HistoryController extends Controller
{
    /**
     * GET /api/history/index?type=historial|guardados|completados&page=1
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Validar tipo
        $type = strtolower($request->query('type', 'historial'));
        if (!in_array($type, ['historial', 'guardados', 'completados'])) {
            return response()->json([
                'ok' => false,
                'message' => "Tipo inválido. Use: historial | guardados | completados."
            ], 422);
        }

        // Subconsultas para % completado (por curso)
        // total de capítulos por curso
        $subTotalChapters = Chapter::query()
            ->selectRaw('modules.course_id AS course_id, COUNT(chapters.id) AS total_chapters')
            ->join('modules', 'modules.id', '=', 'chapters.module_id')
            ->groupBy('modules.course_id');

        // capítulos completados por el usuario por curso
        $subCompletedByUser = CompletedChapter::query()
            ->selectRaw('modules.course_id AS course_id, COUNT(completed_chapters.id) AS completed_count')
            ->join('chapters', 'chapters.id', '=', 'completed_chapters.chapter_id')
            ->join('modules', 'modules.id', '=', 'chapters.module_id')
            ->where('completed_chapters.user_id', $userId)
            ->groupBy('modules.course_id');

        // Subconsulta para certificado más reciente por curso (del usuario)
        $subLatestCertificate = Certificate::query()
            ->selectRaw('registrations.course_id AS course_id, MAX(certificates.created_at) AS latest_cert_created_at')
            ->join('registrations', 'registrations.id', '=', 'certificates.registration_id')
            ->where('registrations.user_id', $userId)
            ->groupBy('registrations.course_id');

        // Query base de cursos con joins de apoyo (miniatura y dueño)
        $coursesBase = Course::query()
            ->leftJoinSub($subTotalChapters, 'tch', 'tch.course_id', '=', 'courses.id')
            ->leftJoinSub($subCompletedByUser, 'cch', 'cch.course_id', '=', 'courses.id')
            ->leftJoin('miniature_courses as mc', 'mc.course_id', '=', 'courses.id')
            ->leftJoin('tutor_courses as tc_owner', function ($join) {
                $join->on('tc_owner.course_id', '=', 'courses.id')
                     ->where('tc_owner.is_owner', true);
            })
            ->leftJoin('users as owner', 'owner.id', '=', 'tc_owner.user_id')
            ->select([
                'courses.id',
                'courses.title',
                'courses.created_at',
                DB::raw('COALESCE(mc.url, NULL) as miniature_url'),
                'owner.id as owner_id',
        'owner.name as owner_name',
        'owner.lastname as owner_lastname',
        'owner.username as owner_username',
        'owner.profile_picture_url as owner_profile_picture_url',
                DB::raw('COALESCE(tch.total_chapters, 0) as total_chapters'),
                DB::raw('COALESCE(cch.completed_count, 0) as completed_chapters')
            ]);

        // Filtrado por tipo
        if ($type === 'historial') {
            // 1) Última visita por curso (ROW_NUMBER sobre ContentView.updated_at)
            $lastPerCourse = ContentView::query()
                ->join('learning_contents as lc', 'lc.id', '=', 'content_views.learning_content_id')
                ->join('chapters', 'chapters.id', '=', 'lc.chapter_id')
                ->join('modules', 'modules.id', '=', 'chapters.module_id')
                ->join('courses', 'courses.id', '=', 'modules.course_id')
                ->where('content_views.user_id', $userId)
                ->selectRaw('
                    courses.id as course_id,
                    chapters.id as chapter_id,
                    chapters.title as chapter_title,
                    content_views.updated_at as seen_at,
                    ROW_NUMBER() OVER (PARTITION BY courses.id ORDER BY content_views.updated_at DESC) as rn
                ')
                ->toBase();

            $lastVisited = DB::query()
                ->fromSub($lastPerCourse, 'lv')
                ->where('lv.rn', 1);

            $query = $coursesBase
                ->joinSub($lastVisited, 'lv2', 'lv2.course_id', '=', 'courses.id')
                ->leftJoinSub($subLatestCertificate, 'lcx', 'lcx.course_id', '=', 'courses.id')
                ->addSelect([
                    DB::raw('ROUND(CASE WHEN COALESCE(tch.total_chapters,0)=0 THEN 0 ELSE (COALESCE(cch.completed_count,0)*100.0/COALESCE(tch.total_chapters,1)) END) as completion_percent'),
                    DB::raw('lv2.chapter_id as last_chapter_id'),
                    DB::raw('lv2.chapter_title as last_chapter_title'),
                    DB::raw('lv2.seen_at as last_seen_at'),
                ])
                ->orderByDesc('lv2.seen_at')
                ->paginate(10);

        } elseif ($type === 'guardados') {
            // 2) Guardados por el usuario, ordenados por SavedCourse.updated_at
            $query = $coursesBase
                ->join('saved_courses as sc', 'sc.course_id', '=', 'courses.id')
                ->leftJoinSub($subLatestCertificate, 'lcx', 'lcx.course_id', '=', 'courses.id')
                ->where('sc.user_id', $userId)
                ->addSelect([
                    DB::raw('ROUND(CASE WHEN COALESCE(tch.total_chapters,0)=0 THEN 0 ELSE (COALESCE(cch.completed_count,0)*100.0/COALESCE(tch.total_chapters,1)) END) as completion_percent'),
                    DB::raw('sc.updated_at as saved_updated_at'),
                ])
                ->orderByDesc('sc.updated_at')
                ->paginate(10);

        } else { // completados
            // 3) Cursos con certificate(s) del usuario (último certificado por curso)
            $certs = Certificate::query()
                ->join('registrations as r', 'r.id', '=', 'certificates.registration_id')
                ->where('r.user_id', $userId)
                ->select([
                    'r.course_id as course_id',
                    'certificates.id as certificate_id',
                    'certificates.code as certificate_code',
                    'certificates.created_at as certificate_created_at',
                    DB::raw('ROW_NUMBER() OVER (PARTITION BY r.course_id ORDER BY certificates.created_at DESC) as rn')
                ]);

            $latestCerts = DB::query()->fromSub($certs, 'cx')->where('cx.rn', 1);

            $query = $coursesBase
                ->joinSub($latestCerts, 'lcx', 'lcx.course_id', '=', 'courses.id')
                ->addSelect([
                    DB::raw('ROUND(CASE WHEN COALESCE(tch.total_chapters,0)=0 THEN 0 ELSE (COALESCE(cch.completed_count,0)*100.0/COALESCE(tch.total_chapters,1)) END) as completion_percent'),
                    DB::raw('lcx.certificate_id'),
                    DB::raw('lcx.certificate_code'),
                    DB::raw('lcx.certificate_created_at'),
                ])
                ->orderByDesc('lcx.certificate_created_at')
                ->paginate(10);
        }

        // Formatear respuesta
        $items = collect($query->items())->map(function ($row) use ($userId, $type) {
            $completion = (int)($row->completion_percent ?? 0);

            // Armar certificado (si existe)
            $certificate = null;
            if ($type === 'completados') {
                $certificate = [
                    'id' => $row->certificate_id ?? null,
                    'code' => $row->certificate_code ?? null,
                    'created_at' => $row->certificate_created_at ?? null,
                ];
            } else {
                // Buscar último certificado (si hay) para el usuario y ese curso
                $cert = Certificate::query()
                    ->join('registrations as r', 'r.id', '=', 'certificates.registration_id')
                    ->where('r.user_id', $userId)
                    ->where('r.course_id', $row->id)
                    ->orderByDesc('certificates.created_at')
                    ->first(['certificates.id', 'certificates.code', 'certificates.created_at']);

                if ($cert) {
                    $certificate = [
                        'id' => $cert->id,
                        'code' => $cert->code,
                        'created_at' => $cert->created_at,
                    ];
                }
            }

            $data = [
                'id' => (int)$row->id,
                'title' => $row->title,
                'miniature_url' => $row->miniature_url,
                'created_at' => $row->created_at,
                'owner' => [
                            'id' => $row->owner_id,
        'name' => $row->owner_name,
        'lastname' => $row->owner_lastname,
        'username' => $row->owner_username,
        'profile_picture_url' => $row->owner_profile_picture_url,
                ],
                'completion_percent' => $completion,    // 0..100
                'certificate' => $certificate,          // objeto o null
            ];

            if ($type === 'historial') {
                $data['last_chapter'] = [
                    'id' => $row->last_chapter_id ?? null,
                    'title' => $row->last_chapter_title ?? null,
                ];
                $data['last_seen_at'] = $row->last_seen_at ?? null;
            }

            if ($type === 'guardados') {
                $data['saved_updated_at'] = $row->saved_updated_at ?? null;
            }

            return $data;
        });

        return response()->json([
            'ok' => true,
            'type' => $type,
            'per_page' => $query->perPage(),
            'current_page' => $query->currentPage(),
            'next_page_url' => $query->nextPageUrl(),
            'prev_page_url' => $query->previousPageUrl(),
            'total' => $query->total(),
            'data' => $items,
        ]);
    }
}
