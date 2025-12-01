<?php

namespace App\Http\Controllers;

use App\Models\EducationalUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EducationalUnitController extends Controller
{
    /**
     * INDEX LIGERO
     * Devuelve todas las unidades educativas sin filtros
     * ni datos adicionales (sin conteos, sin niveles, etc.).
     */
    public function index()
    {
        $units = EducationalUnit::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de unidades educativas obtenida correctamente.',
            'data'    => $units,
        ], 200);
    }

    /**
     * INDEX ADMIN
     * - filtros: name, organization_domain
     * - paginación: page, per_page
     * - datos extra por unidad:
     *     * users_count   → cantidad de usuarios (EducationalUser a través de sedes)
     *     * sedes_count   → cantidad de sedes
     *     * educational_levels → arreglo de niveles educativos asociados
     */
    public function indexAdmin(Request $request) 
    {
        $name   = $request->input('name');
        $domain = $request->input('organization_domain');
        $levelId = $request->input('educational_level_id'); // 👈 nuevo filtro
        $perPage = (int) $request->input('per_page', 10);

        if ($perPage <= 0) {
            $perPage = 10;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        $query = EducationalUnit::query()
            ->withCount([
                // sedes por unidad
                'sedes as sedes_count',
                // usuarios educativos por unidad (hasManyThrough via sedes)
                'educationalUsers as users_count',
            ])
            // cargamos los niveles educativos completos
            ->with(['educationalLevels:id,name,description,period,max_periods'])
            ->when($name, function ($q) use ($name) {
                $q->where('name', 'LIKE', '%' . $name . '%');
            })
            ->when($domain, function ($q) use ($domain) {
                $q->where('organization_domain', 'LIKE', '%' . $domain . '%');
            })
            // 🔍 nuevo filtro por nivel educativo (belongsToMany)
            ->when($levelId, function ($q) use ($levelId) {
                $q->whereHas('educationalLevels', function ($sub) use ($levelId) {
                    $sub->where('educational_levels.id', (int) $levelId);
                });
            })
            ->orderBy('name', 'asc');

        $units = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lista administrativa de unidades educativas obtenida correctamente.',
            'data'    => $units->items(),
            'meta'    => [
                'current_page'        => $units->currentPage(),
                'per_page'            => $units->perPage(),
                'total'               => $units->total(),
                'last_page'           => $units->lastPage(),
                'from'                => $units->firstItem(),
                'to'                  => $units->lastItem(),
                'name'                => $name,
                'organization_domain' => $domain,
                'educational_level_id'=> $levelId, // 👈 por si lo quieres leer en el front
            ],
        ], 200);
    } 

    /**
     * STORE
     * Crear una nueva unidad educativa, asociando niveles educativos.
     * - Requiere al menos 1 educational_level_id.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255|unique:educational_units,name',
                'organization_domain' => 'nullable|string|max:255|unique:educational_units,organization_domain',
                'url_logo' => 'required|url|max:255',

                'educational_level_ids'   => 'required|array|min:1',
                'educational_level_ids.*' => 'integer|exists:educational_levels,id',
            ],
            [
                'name.required' => 'El nombre de la unidad educativa es obligatorio.',
                'name.string'   => 'El nombre de la unidad educativa debe ser una cadena de texto.',
                'name.max'      => 'El nombre de la unidad educativa no debe superar los 255 caracteres.',
                'name.unique'   => 'Ya existe una unidad educativa con ese nombre.',

                'organization_domain.string' => 'El dominio de la organización debe ser una cadena de texto.',
                'organization_domain.max'    => 'El dominio de la organización no debe superar los 255 caracteres.',
                'organization_domain.unique' => 'Ya existe una unidad educativa con ese dominio.',

                'url_logo.required' => 'La URL del logo es obligatoria.',
                'url_logo.url'      => 'Debe proporcionar una URL válida para el logo.',
                'url_logo.max'      => 'La URL del logo no debe superar los 255 caracteres.',

                'educational_level_ids.required' => 'Debe seleccionar al menos un nivel educativo.',
                'educational_level_ids.array'    => 'El campo de niveles educativos debe ser un arreglo.',
                'educational_level_ids.min'      => 'Debe seleccionar al menos un nivel educativo.',
                'educational_level_ids.*.integer'=> 'Cada nivel educativo debe ser un ID numérico.',
                'educational_level_ids.*.exists' => 'Alguno de los niveles educativos seleccionados no existe.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $levelIds = $data['educational_level_ids'];
        unset($data['educational_level_ids']);

        $educationalUnit = EducationalUnit::create($data);

        // Asociar niveles educativos en la tabla pivote unit_levels
        $educationalUnit->educationalLevels()->sync($levelIds);

        // Recargar con niveles
        $educationalUnit->load('educationalLevels:id,name,description,period,max_periods');

        return response()->json([
            'success' => true,
            'message' => 'Unidad educativa creada correctamente.',
            'data'    => $educationalUnit,
        ], 201);
    }

    /**
     * UPDATE
     * Actualizar una unidad educativa, incluyendo sus niveles educativos.
     */
    public function update(Request $request, EducationalUnit $educationalUnit)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255|unique:educational_units,name,' . $educationalUnit->id,
                'organization_domain' => 'nullable|string|max:255|unique:educational_units,organization_domain,' . $educationalUnit->id,
                'url_logo' => 'required|url|max:255',

                'educational_level_ids'   => 'required|array|min:1',
                'educational_level_ids.*' => 'integer|exists:educational_levels,id',
            ],
            [
                'name.required' => 'El nombre de la unidad educativa es obligatorio.',
                'name.string'   => 'El nombre de la unidad educativa debe ser una cadena de texto.',
                'name.max'      => 'El nombre de la unidad educativa no debe superar los 255 caracteres.',
                'name.unique'   => 'Ya existe otra unidad educativa con este nombre.',

                'organization_domain.string' => 'El dominio de la organización debe ser una cadena de texto.',
                'organization_domain.max'    => 'El dominio de la organización no debe superar los 255 caracteres.',
                'organization_domain.unique' => 'Ya existe otra unidad educativa con este dominio.',

                'url_logo.required' => 'La URL del logo es obligatoria.',
                'url_logo.url'      => 'Debe proporcionar una URL válida para el logo.',
                'url_logo.max'      => 'La URL del logo no debe superar los 255 caracteres.',

                'educational_level_ids.required' => 'Debe seleccionar al menos un nivel educativo.',
                'educational_level_ids.array'    => 'El campo de niveles educativos debe ser un arreglo.',
                'educational_level_ids.min'      => 'Debe seleccionar al menos un nivel educativo.',
                'educational_level_ids.*.integer'=> 'Cada nivel educativo debe ser un ID numérico.',
                'educational_level_ids.*.exists' => 'Alguno de los niveles educativos seleccionados no existe.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $levelIds = $data['educational_level_ids'];
        unset($data['educational_level_ids']);

        $educationalUnit->update($data);

        // Actualizar niveles educativos asociados
        $educationalUnit->educationalLevels()->sync($levelIds);

        $educationalUnit->load('educationalLevels:id,name,description,period,max_periods');

        return response()->json([
            'success' => true,
            'message' => 'Unidad educativa actualizada correctamente.',
            'data'    => $educationalUnit,
        ], 200);
    }

    /**
     * DELETE
     */
    public function destroy(EducationalUnit $educationalUnit)
    {
        $educationalUnit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unidad educativa eliminada correctamente.',
        ], 200);
    }
}
