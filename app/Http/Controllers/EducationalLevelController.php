<?php

namespace App\Http\Controllers;

use App\Models\EducationalLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EducationalLevelController extends Controller
{
    /**
     * INDEX LIGERO
     * Devuelve todos los niveles educativos sin filtros ni datos extra.
     */
    public function index()
    {
        $levels = EducationalLevel::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de niveles educativos obtenida correctamente.',
            'data'    => $levels,
        ], 200);
    }

    /**
     * INDEX ADMIN
     * - filtros: name, period, max_periods
     * - paginación: page, per_page
     * - datos extra:
     *     * users_count              → cantidad de usuarios en ese nivel
     *     * educational_units_count  → cantidad de unidades educativas que tienen ese nivel
     */
    public function indexAdmin(Request $request)
    {
        $name       = $request->input('name');
        $period     = $request->input('period');
        $maxPeriods = $request->input('max_periods');
        $perPage    = (int) $request->input('per_page', 10);

        if ($perPage <= 0) {
            $perPage = 10;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        $query = EducationalLevel::query()
            ->withCount([
                'educationalUsers as users_count',
                'educationalUnits as educational_units_count',
            ])
            ->when($name, function ($q) use ($name) {
                $q->where('name', 'LIKE', '%' . $name . '%');
            })
            ->when($period, function ($q) use ($period) {
                $q->where('period', 'LIKE', '%' . $period . '%');
            })
            ->when($maxPeriods, function ($q) use ($maxPeriods) {
                $q->where('max_periods', (int) $maxPeriods);
            })
            ->orderBy('name', 'asc');

        $levels = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lista administrativa de niveles educativos obtenida correctamente.',
            'data'    => $levels->items(),
            'meta'    => [
                'current_page' => $levels->currentPage(),
                'per_page'     => $levels->perPage(),
                'total'        => $levels->total(),
                'last_page'    => $levels->lastPage(),
                'from'         => $levels->firstItem(),
                'to'           => $levels->lastItem(),
                'name'         => $name,
                'period'       => $period,
                'max_periods'  => $maxPeriods,
            ],
        ], 200);
    }

    /**
     * STORE
     * Crear un nuevo nivel educativo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'        => 'required|string|max:255|unique:educational_levels,name',
                'description' => 'nullable|string',
                'period'      => 'required|string|max:255',
                'max_periods' => 'required|integer|min:1',
            ],
            [
                'name.required' => 'El nombre del nivel educativo es obligatorio.',
                'name.string'   => 'El nombre del nivel educativo debe ser una cadena de texto.',
                'name.max'      => 'El nombre del nivel educativo no debe superar los 255 caracteres.',
                'name.unique'   => 'Ya existe un nivel educativo con ese nombre.',

                'description.string' => 'La descripción debe ser una cadena de texto.',

                'period.required' => 'El periodo es obligatorio.',
                'period.string'   => 'El periodo debe ser una cadena de texto.',
                'period.max'      => 'El periodo no debe superar los 255 caracteres.',

                'max_periods.required' => 'El número máximo de periodos es obligatorio.',
                'max_periods.integer'  => 'El número máximo de periodos debe ser un número entero.',
                'max_periods.min'      => 'El número máximo de periodos debe ser al menos 1.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $level = EducationalLevel::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Nivel educativo creado correctamente.',
            'data'    => $level,
        ], 201);
    }

    /**
     * UPDATE
     * Actualizar un nivel educativo existente.
     */
    public function update(Request $request, EducationalLevel $educationalLevel)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'        => 'required|string|max:255|unique:educational_levels,name,' . $educationalLevel->id,
                'description' => 'nullable|string',
                'period'      => 'required|string|max:255',
                'max_periods' => 'required|integer|min:1',
            ],
            [
                'name.required' => 'El nombre del nivel educativo es obligatorio.',
                'name.string'   => 'El nombre del nivel educativo debe ser una cadena de texto.',
                'name.max'      => 'El nombre del nivel educativo no debe superar los 255 caracteres.',
                'name.unique'   => 'Ya existe otro nivel educativo con este nombre.',

                'description.string' => 'La descripción debe ser una cadena de texto.',

                'period.required' => 'El periodo es obligatorio.',
                'period.string'   => 'El periodo debe ser una cadena de texto.',
                'period.max'      => 'El periodo no debe superar los 255 caracteres.',

                'max_periods.required' => 'El número máximo de periodos es obligatorio.',
                'max_periods.integer'  => 'El número máximo de periodos debe ser un número entero.',
                'max_periods.min'      => 'El número máximo de periodos debe ser al menos 1.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $educationalLevel->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Nivel educativo actualizado correctamente.',
            'data'    => $educationalLevel,
        ], 200);
    }

    /**
     * DESTROY
     * Eliminar un nivel educativo.
     */
    public function destroy(EducationalLevel $educationalLevel)
    {
        $educationalLevel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nivel educativo eliminado correctamente.',
        ], 200);
    }
}
