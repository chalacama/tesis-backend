<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Course;
use Illuminate\Http\Request;
;

class ModuleController extends Controller
{

public function createModule(Request $request)
{
    // 1. Validar la petición. La regla 'exists' ya confirma que el curso es válido.
    // Laravel se encarga de la respuesta de error si la validación falla.
    $validated = $request->validate([
        'course_id' => 'required|exists:courses,id',
        'name'      => 'required|string|max:255',
        'order'     => 'required|integer'
        
    ]);

    // 2. Crear el módulo usando asignación masiva.
    $module = Module::create($validated);

    // 3. Devolver la respuesta de éxito.
    return response()->json([
        'message' => 'Módulo creado correctamente',
        'module'  => $module
    ], 201);
}
    public function updateModule(Request $request, $id)
{
    // 1. Validar solo los datos que vienen en la petición.
    // Laravel se encarga de la respuesta de error 422 si la validación falla.
    $validatedData = $request->validate([
        'name'      => 'sometimes|string|max:255',
        'order'     => 'sometimes|integer',
        
    ]);

    $module = Module::find($id);

        if (!$module) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
    // 2. Actualizar el módulo con los datos validados.
    $module->update($validatedData);

    // 3. Devolver la respuesta de éxito con el módulo actualizado.
    return response()->json([
        'message' => 'Módulo actualizado correctamente',
        'module'  => $module
    ], 200);
}
public function activateModule(Request $request, $id)
{
    $validated = $request->validate([
        'activate' => 'required|boolean',
    ]);

    $module = Module::find($id);

    if (!$module) {
        return response()->json(['message' => 'Módulo no encontrado'], 404);
    }
    if ($validated['activate'] && $module->enabled) {
        return response()->json([
            'message' => 'El módulo ya está activado',
            'module' => $module
        ]);
    }
    if (!$validated['activate'] && !$module->enabled) {
        return response()->json([
            'message' => 'El módulo ya está desactivado',
            'module' => $module
        ]);
    }
    if ($validated['activate']) {
        $module->enabled = true;
    } else {
        $module->enabled = false;
    }

    $module->save();

    return response()->json([
        'message' => $validated['activate'] ? 'Módulo activado correctamente' : 'Módulo desactivado correctamente',
        'module' => $module
    ]);
}
public function softDeleteModule($id)
{
    $module = Module::find($id);

    if (!$module) {
        return response()->json(['message' => 'Módulo no encontrado'], 404);
    }

    $module->delete();

    return response()->json([
        'message' => 'Módulo enviado a papelería correctamente'
    ]);
}
}
