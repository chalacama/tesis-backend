<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Models\Sede;
use App\Models\EducationalUnit;
use App\Services\EcuadorLocationService;

class SedeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Listado de sedes visibles para el usuario autenticado.
     * Aplica filtro por dominio (no admin) y añade nombres de provincia/cantón.
     */
    public function index(Request $request, EcuadorLocationService $locations): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Base query con relaciones
        $query = Sede::with([
            'educationalUnit.educationalLevels', // niveles de educación
            'careers',                           // carreras por sede
        ]);

        // Si es admin, ve todo sin filtros por dominio
        if (!$user->hasRole('admin')) {
            $email = $user->email;
            $domain = null;

            if ($email && str_contains($email, '@')) {
                $domain = strtolower(substr(strrchr($email, '@'), 1));
            }

            if ($domain === 'gmail.com') {
                // Solo sedes cuya unidad educativa NO tiene dominio
                $query->whereHas('educationalUnit', function ($q) {
                    $q->whereNull('organization_domain');
                });
            } elseif ($domain) {
                // Dominio institucional
                $existsDomain = EducationalUnit::where('organization_domain', $domain)->exists();

                if ($existsDomain) {
                    // Mostrar solo sedes de unidades con ese dominio
                    $query->whereHas('educationalUnit', function ($q) use ($domain) {
                        $q->where('organization_domain', $domain);
                    });
                } else {
                    // Dominio no registrado → no mostrar sedes
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Sin dominio legible → no mostramos nada
                $query->whereRaw('1 = 0');
            }
        }

        $sedes = $query->get();

        // Transformar para incluir nombres de provincia/cantón
        $data = $sedes->map(function (Sede $sede) use ($locations) {
            return $this->transformSede($sede, $locations, false);
        });

        return response()->json([
            'sedes' => $data,
        ]);
    }

    /**
     * Listado global de sedes con:
     * - TODOS los registros
     * - Conteo de usuarios por sede
     * - Filtros por unidad educativa, provincia y cantón
     * - Paginación
     */
    public function indexAll(Request $request, EcuadorLocationService $locations): JsonResponse
    {
        $query = Sede::with([
            'educationalUnit.educationalLevels',
            'careers',
        ])->withCount('educationalUsers as users_count');

        // Filtro por nombre de la unidad educativa
        if ($request->filled('unit_name')) {
            $name = $request->string('unit_name');
            $query->whereHas('educationalUnit', function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
        }

        // Filtro por province_id
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->input('province_id'));
        }

        // Filtro por canton_id
        if ($request->filled('canton_id')) {
            $query->where('canton_id', $request->input('canton_id'));
        }

        // Paginación
        $perPage = (int) $request->input('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }

        $paginator = $query->paginate($perPage);

        // Transformamos la colección para agregar nombres de provincia/cantón
        $transformed = $paginator->getCollection()->map(function (Sede $sede) use ($locations) {
            return $this->transformSede($sede, $locations, true);
        });

        // Reemplazamos la colección interna por la transformada
        $paginator->setCollection($transformed);

        // El paginador ya trae meta: current_page, last_page, total, data, etc.
        return response()->json($paginator);
    }

    /**
     * Helper privado para formatear una sede con:
     * - IDs y nombres de provincia/cantón
     * - Relaciones (educationalUnit, careers, educationalLevels)
     * - Opcional: users_count
     */
    private function transformSede(Sede $sede, EcuadorLocationService $locations, bool $includeUsersCount = false): array
    {
        $provinceName = null;
        $cantonName   = null;

        if ($sede->province_id) {
            // Buscar nombre de provincia
            $provinces = $locations->getProvinces();
            $province = collect($provinces)->firstWhere('id', (string) $sede->province_id);
            if ($province) {
                $provinceName = $province['name'];
            }

            // Buscar nombre de cantón si hay canton_id
            if ($sede->canton_id) {
                $cantons = $locations->getCantons((string) $sede->province_id);
                $canton  = collect($cantons)->firstWhere('id', (string) $sede->canton_id);
                if ($canton) {
                    $cantonName = $canton['name'];
                }
            }
        }

        $base = [
            'id'            => $sede->id,
            'contry'        => $sede->contry,
            'province_id'   => $sede->province_id,
            'province_name' => $provinceName,
            'canton_id'     => $sede->canton_id,
            'canton_name'   => $cantonName,

            // Relaciones completas
            'educational_unit' => $sede->educationalUnit,
            'careers'          => $sede->careers,

            'created_at' => $sede->created_at,
            'updated_at' => $sede->updated_at,
        ];

        if ($includeUsersCount) {
            $base['users_count'] = $sede->users_count ?? 0;
        }

        return $base;
    }
}
