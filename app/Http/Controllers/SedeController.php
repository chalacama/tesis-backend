<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;

use App\Models\Sede;
use App\Models\EducationalUnit;
use App\Services\EcuadorLocationService;

class SedeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Listado de sedes visibles para el usuario autenticado (no admin).
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

        $data = $sedes->map(function (Sede $sede) use ($locations) {
            return $this->transformSede($sede, $locations, false);
        });

        return response()->json([
            'sedes' => $data,
        ]);
    }

    /**
     * INDEX ADMIN
     * - TODOS los registros
     * - Conteo de usuarios por sede
     * - Filtros por:
     *   - unit_name (nombre de la unidad educativa)
     *   - province_id
     *   - canton_id
     *   - educational_level_id (NUEVO, filtra por nivel educativo de la unidad)
     * - Paginación
     */
    public function indexAdmin(Request $request, EcuadorLocationService $locations): JsonResponse
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

        // 🔹 NUEVO: filtro por nivel educativo
        // Sedes cuya UNIDAD EDUCATIVA tenga ese nivel en educationalLevels
        if ($request->filled('educational_level_id')) {
            $levelId = (int) $request->input('educational_level_id');
            $query->whereHas('educationalUnit.educationalLevels', function ($q) use ($levelId) {
                $q->where('educational_levels.id', $levelId);
            });
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

        $paginator->setCollection($transformed);

        return response()->json($paginator);
    }

/**
 * STORE
 * Crear una nueva sede.
 * Recibe IDs de provincia, cantón, unidad educativa y opcionalmente carreras.
 */
public function store(Request $request, EcuadorLocationService $locations): JsonResponse
{
    $validator = Validator::make(
        $request->all(),
        [
            
            // 🔹 Ya NO recibimos "contry"
            'province_id'          => 'required|integer',
            'canton_id'            => 'required|integer',
            'educational_unit_id'  => 'required|exists:educational_units,id',

            // 🔹 OPCIONAL: carreras para la sede
            'career_ids'           => 'sometimes|array',
            'career_ids.*'         => 'integer|exists:careers,id',
        ],
        [
            'province_id.required' => 'La provincia es obligatoria.',
            'province_id.integer'  => 'La provincia debe ser un ID numérico válido.',

            'canton_id.required'   => 'El cantón es obligatorio.',
            'canton_id.integer'    => 'El cantón debe ser un ID numérico válido.',

            'educational_unit_id.required' => 'La unidad educativa es obligatoria.',
            'educational_unit_id.exists'   => 'La unidad educativa seleccionada no existe.',

            'career_ids.array'     => 'Las carreras deben enviarse como un arreglo de IDs.',
            'career_ids.*.integer' => 'Cada carrera debe ser un ID numérico válido.',
            'career_ids.*.exists'  => 'Alguna de las carreras seleccionadas no existe.',
        ]
    );

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $validated  = $validator->validated();
    $careerIds  = $validated['career_ids'] ?? null;
    unset($validated['career_ids']); // No es columna de la tabla sedes

    // contry se pone solo con el default 'ECUADOR' de la migración
    $sede = Sede::create($validated);

    // Asociar carreras si se enviaron
    if (!is_null($careerIds)) {
        $sede->careers()->sync($careerIds);
    }

    // Cargamos relaciones para devolver todo completo
    $sede->load([
        'educationalUnit.educationalLevels',
        'careers',
    ]);

    $data = $this->transformSede($sede, $locations, true);

    return response()->json([
        'success' => true,
        'message' => 'Sede creada correctamente.',
        'data'    => $data,
    ], 201);
}

/**
 * UPDATE
 * Actualizar una sede existente.
 * También permite actualizar (o limpiar) sus carreras opcionalmente.
 */
public function update(Request $request, Sede $sede, EcuadorLocationService $locations): JsonResponse
{
    $validator = Validator::make(
        $request->all(),
        [
            // 🔹 Ya NO recibimos "contry"

            'province_id'          => 'required|integer',
            'canton_id'            => 'required|integer',
            'educational_unit_id'  => 'required|exists:educational_units,id',

            // 🔹 OPCIONAL: carreras para la sede
            'career_ids'           => 'sometimes|array',
            'career_ids.*'         => 'integer|exists:careers,id',
        ],
        [
            'province_id.required' => 'La provincia es obligatoria.',
            'province_id.integer'  => 'La provincia debe ser un ID numérico válido.',

            'canton_id.required'   => 'El cantón es obligatorio.',
            'canton_id.integer'    => 'El cantón debe ser un ID numérico válido.',

            'educational_unit_id.required' => 'La unidad educativa es obligatoria.',
            'educational_unit_id.exists'   => 'La unidad educativa seleccionada no existe.',

            'career_ids.array'     => 'Las carreras deben enviarse como un arreglo de IDs.',
            'career_ids.*.integer' => 'Cada carrera debe ser un ID numérico válido.',
            'career_ids.*.exists'  => 'Alguna de las carreras seleccionadas no existe.',
        ]
    );

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $validated = $validator->validated();

    // Solo modificamos carreras si el campo viene en el request
    $careerIds = array_key_exists('career_ids', $validated)
        ? $validated['career_ids']
        : null;

    unset($validated['career_ids']); // no es columna de la tabla

    // contry no se toca, se queda con el valor actual (ECUADOR)
    $sede->update($validated);

    if (!is_null($careerIds)) {
        // [] → limpia todas las carreras
        // [1,2,3] → sincroniza esas carreras
        $sede->careers()->sync($careerIds);
    }

    $sede->load([
        'educationalUnit.educationalLevels',
        'careers',
    ]);

    $data = $this->transformSede($sede, $locations, true);

    return response()->json([
        'success' => true,
        'message' => 'Sede actualizada correctamente.',
        'data'    => $data,
    ], 200);
}



    /**
     * DESTROY
     * Eliminar una sede.
     */
    public function destroy(Sede $sede): JsonResponse
    {
        $sede->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sede eliminada correctamente.',
        ], 200);
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
            $provinces = $locations->getProvinces();
            $province = collect($provinces)->firstWhere('id', (string) $sede->province_id);
            if ($province) {
                $provinceName = $province['name'];
            }

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

