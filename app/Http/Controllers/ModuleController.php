<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class ModuleController extends Controller
{
use AuthorizesRequests;
public function store(Request $request): JsonResponse
{
    $this->authorize('create', Module::class);
    $validated = $request->validate([
        'course_id' => 'required|exists:courses,id',
        'name'      => 'required|string|max:255',
    ]);

    $course = Course::findOrFail($validated['course_id']);
    // Verifica que el usuario pueda crear módulos en ese curso
    $this->authorize('update', $course);

    $module = Module::create($validated);

    return response()->json([
        'message' => 'Módulo creado correctamente',
        'module'  => $module
    ], 201);
}

public function update(Request $request, Module $module)
{
    $this->authorize('update', $module);

    $validatedData = $request->validate([
        'name' => 'sometimes|string|max:255',
    ]);

    $module->update($validatedData);

    return response()->json([
        'message' => 'Módulo actualizado correctamente',
        'module'  => $module
    ], 200);
}

public function archived(Module $module)
{
    $this->authorize('delete', $module);

    $module->delete();

    return response()->json([
        'message' => 'Módulo enviado a papelería correctamente'
    ]);
}

public function reorder(Request $request)
{
    $this->authorize('update', Module::class);

    $validated = $request->validate([
        'modules'   => 'required|array',
        'modules.*' => 'integer|exists:modules,id',
    ]);

    Module::setNewOrder($validated['modules']);

    return response()->json([
        'message' => 'El orden de los módulos ha sido actualizado.'
    ]);
}
}
