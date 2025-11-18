<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * SHOW
     *  - Público.
     *  - Permite validar/ver un certificado a partir de su código.
     *  - Devuelve todos los datos que pediste:
     *      curso, dueño del curso, dueño del certificado, info académica, etc.
     */
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code = $request->input('code');

        // No necesitamos $request->user() porque la ruta es pública
        $certificate = $this->findCertificateByCode($code);

        return response()->json([
            'success' => true,
            'data'    => $this->transformCertificate($certificate),
        ]);
    }

    /**
     * INDEX
     *  - Solo el dueño (usuario autenticado) ve sus certificados.
     *  - Soporta scroll infinito con paginación:
     *      ?page=1&per_page=10
     *  - Filtros:
     *      ?search=curso
     *      ?from_date=2025-01-01
     *      ?to_date=2025-12-31
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'search'    => ['nullable', 'string'],
            'from_date' => ['nullable', 'date'],
            'to_date'   => ['nullable', 'date'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage   = $request->integer('per_page', 10);
        $search    = $request->input('search');
        $fromDate  = $request->input('from_date');
        $toDate    = $request->input('to_date');

        $query = Certificate::query()
            // certificados del usuario autenticado
            ->whereHas('registration', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with($this->certificateRelations());

        // filtro por título de curso
        if ($search) {
            $query->whereHas('registration.course', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }

        // rango de fechas del certificado (created_at del certificado)
        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // ideal para infinite scroll
        $paginator = $query
            ->orderByDesc('created_at')
            ->simplePaginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $paginator->getCollection()->map(function (Certificate $certificate) {
                return $this->transformCertificate($certificate);
            }),
            'meta'    => [
                'current_page'  => $paginator->currentPage(),
                'per_page'      => $paginator->perPage(),
                'next_page'     => $paginator->hasMorePages()
                    ? $paginator->currentPage() + 1
                    : null,
                'next_page_url' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * DOWNLOAD
     *  - CUALQUIERA con el código puede descargarlo (público).
     *  - Devuelve el archivo (PDF) del certificado.
     *  - Pensado para usarlo en Angular 19 con responseType: 'blob'.
     *
     *  Ejemplo Angular:
     *    this.http.get('/api/certificate/download?code=XXXX', {
     *      responseType: 'blob'
     *    })
     */
    public function download(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code = $request->input('code');

        // Cargamos el certificado sólo para validar que existe
        $certificate = Certificate::where('code', $code)->firstOrFail();

        // Ruta donde guardas el PDF del certificado
        // Ajusta esto según tu implementación real.
        $fileName = 'certificate-' . $certificate->code . '.pdf';
        $path     = 'certificates/' . $fileName; // storage/app/public/certificates/...

        if (!Storage::disk('public')->exists($path)) {
            // Si NO guardas un PDF real en el servidor,
            // aquí deberías:
            //  - o bien generar el PDF al vuelo (DOMPDF, Snappy, etc.),
            //  - o devolver 404 y manejar la descarga sólo desde el front.
            abort(404, 'El archivo del certificado no existe en el servidor.');
        }

        return Storage::disk('public')->download($path, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // =========================================================
    // ================ MÉTODOS PRIVADOS ========================
    // =========================================================

    /**
     * Relaciones comunes para cargar toda la info del certificado.
     */
    private function certificateRelations(): array
    {
        return [
            'registration',
            'registration.course',
            'registration.course.miniature',
            'registration.course.difficulty',
            'registration.course.categories',
            'registration.course.owner', // dueño del curso
            'registration.user',
            'registration.user.educationalUser',
            'registration.user.educationalUser.sede',
            'registration.user.educationalUser.sede.educationalUnit',
            'registration.user.educationalUser.career',
        ];
    }

    /**
     * Buscar un certificado por código con todas las relaciones necesarias.
     */
    private function findCertificateByCode(string $code): Certificate
    {
        return Certificate::with($this->certificateRelations())
            ->where('code', $code)
            ->firstOrFail();
    }

    /**
     * Transformar un usuario a un array público.
     * Reutilizado para:
     *  - dueño del curso
     *  - dueño del certificado
     */
    private function transformUser(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id'                  => $user->id,
            'name'                => $user->name,
            'lastname'            => $user->lastname,
            'username'            => $user->username,
            'profile_picture_url' => $user->profile_picture_url,
        ];
    }

    /**
     * Armar la estructura de datos de salida del certificado
     * (se usa tanto en show como en index).
     * Ahora el certificado es público: cualquiera puede descargar,
     * así que can_download siempre es true mientras exista el certificado.
     */
    private function transformCertificate(Certificate $certificate): array
    {
        $registration = $certificate->registration;
        $course       = $registration->course;
        $student      = $registration->user;

        // dueño del curso (owner es una relación belongsToMany filtrada)
        $owner = null;
        if ($course && $course->relationLoaded('owner')) {
            $owner = $course->owner->first(); // owner es una colección
        } elseif ($course) {
            $owner = $course->owner()->first();
        }

        // info académica del estudiante
        $educationalUser = $student?->educationalUser;
        $sede            = $educationalUser?->sede;
        $educationalUnit = $sede?->educationalUnit;
        $career          = $educationalUser?->career;

        return [
            'course' => [
                'id'             => $course?->id,
                'title'          => $course?->title,
                'miniature_url'  => $course?->miniature?->url,
                'difficulty'     => $course?->difficulty?->name,
                'categories'     => $course
                    ? $course->categories->map(function ($category) {
                        return [
                            'id'   => $category->id,
                            'name' => $category->name,
                        ];
                    })->values()
                    : [],
                'created_at'     => optional($course?->created_at)?->toIso8601String(),
            ],

            'certificate' => [
                'id'          => $certificate->id,
                'code'        => $certificate->code,
                'date'        => optional($certificate->created_at)?->toIso8601String(),
                'total_score' => $certificate->total_score, // puntuación del certificado
            ],

            // dueño del curso
            'course_owner' => $this->transformUser($owner),

            // dueño del certificado (estudiante)
            'certificate_owner' => $this->transformUser($student),

            // información académica del dueño del certificado
            'academic_information' => [
                'sede' => $sede ? [
                    'id'       => $sede->id,
                    'province' => $sede->province,
                    'canton'   => $sede->canton,
                ] : null,

                'educational_unit' => $educationalUnit ? [
                    'id'                  => $educationalUnit->id,
                    'name'                => $educationalUnit->name,
                    'url_logo'            => $educationalUnit->url_logo,
                    'organization_domain' => $educationalUnit->organization_domain,
                ] : null,

                'career' => $career ? [
                    'id'       => $career->id,
                    'name'     => $career->name,
                    'url_logo' => $career->url_logo ?? null,
                ] : null,
            ],

            // Ahora cualquier persona con el código puede descargarlo
            'can_download' => true,
        ];
    }
}
