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
