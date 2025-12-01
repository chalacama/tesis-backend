<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCareerRequest;
use App\Http\Requests\UpdateCareerRequest;
use Illuminate\Http\Request;
use App\Models\Career;
class CareerController extends Controller
{
    public function index()
    {
        $careers = Career::orderBy('name', 'asc')->get();

        return response()->json([
                'success' => true,
                'message' => 'Lista de carreras obtenida correctamente.',
                'data'    => $careers
        ], 200);
    }
    /**
     * Index administrativo:
     *  - filtros: name, max_semesters
     *  - paginación: page, per_page
     *  - datos extra:
     *      * courses_count
     *      * users_count
     *      * sedes_count
     */
    public function indexAdmin(Request $request)
    {
        $name         = $request->input('name');
        $maxSemesters = $request->input('max_semesters');
        $perPage      = (int) $request->input('per_page', 10);

        // Limitar per_page
        if ($perPage <= 0) {
            $perPage = 10;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        $query = Career::query()
            ->withCount([
                // cantidad de cursos asociados a la carrera
                'courses as courses_count',
                // cantidad de usuarios asociados (EducationalUser)
                'educationalUsers as users_count',
                // cantidad de sedes que tienen esa carrera
                'careerSedes as sedes_count',
            ])
            ->when($name, function ($q) use ($name) {
                $q->where('name', 'LIKE', '%' . $name . '%');
            })
            ->when($maxSemesters, function ($q) use ($maxSemesters) {
                $q->where('max_semesters', $maxSemesters);
            })
            ->orderBy('name', 'asc');

        $careers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lista administrativa de carreras obtenida correctamente.',
            'data'    => $careers->items(),
            'meta'    => [
                'current_page'  => $careers->currentPage(),
                'per_page'      => $careers->perPage(),
                'total'         => $careers->total(),
                'last_page'     => $careers->lastPage(),
                'from'          => $careers->firstItem(),
                'to'            => $careers->lastItem(),
                'name'          => $name,
                'max_semesters' => $maxSemesters,
            ],
        ], 200);
    }

      /**
     * Crear una nueva carrera.
     */
    public function store(StoreCareerRequest $request)
    {
        $career = Career::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Carrera creada correctamente',
            'data' => $career
        ], 201);
    }

    /**
     * Actualizar una carrera existente.
     */
    public function update(UpdateCareerRequest $request, Career $career)
    {
        $career->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Carrera actualizada correctamente',
            'data' => $career
        ], 200);
    }

    /**
     * Eliminar una carrera.
     */
    public function destroy(Career $career)
    {
        $career->delete();

        return response()->json([
            'success' => true,
            'message' => 'Carrera eliminada correctamente'
        ], 200);
    }
}
