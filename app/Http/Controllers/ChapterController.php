<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
class ChapterController extends Controller
{
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'module_id' => 'required|integer',
    ]);

    $chapter = Chapter::create($validated);

    return response()->json([
        'message' => 'Capítulo creado correctamente',
        'chapter' => $chapter
    ]);
}
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'title' => 'required|string',
        'description' => 'nullable|string',
        'module_id' => 'required|integer',
    ]);

    $chapter = Chapter::find($id);

    if (!$chapter) {
        return response()->json(['message' => 'Capítulo no encontrado'], 404);
    }

    $chapter->update($validated);

    return response()->json([
        'message' => 'Capítulo actualizado correctamente',
        'chapter' => $chapter
    ]);
}

public function archived($id)
{
    $chapter = Chapter::find($id);

    if (!$chapter) {
        return response()->json(['message' => 'Capítulo no encontrado'], 404);
    }

    $chapter->delete();

    return response()->json([
        'message' => 'Capítulo eliminado correctamente'
    ]);
}
public function reorder(Request $request)
{
    // 1. Validar que recibimos un array de IDs.
    $validated = $request->validate([
        'chapters'   => 'required|array',
        'chapters.*' => 'integer|exists:chapters,id', // Valida que cada ID exista en la tabla modules
    ]);

    // 2. Llama al método estático del modelo para reordenar.
    Chapter::setNewOrder($validated['chapters']);

    // 3. Devuelve una respuesta de éxito.
    return response()->json([
        'message' => 'El orden de los capitulos ha sido actualizado.'
    ]);
}
public function activate(Request $request, $id)
{
    $validated = $request->validate([
        'activate' => 'required|boolean',
    ]);

    $chapter = Chapter::find($id);

    if (!$chapter) {
        return response()->json(['message' => 'Capítulo no encontrado'], 404);
    }

    if ($validated['activate'] && $chapter->enabled) {
        return response()->json([
            'message' => 'El capítulo ya está activado',
            'chapter' => $chapter
        ]);
    }

    if (!$validated['activate'] && !$chapter->enabled) {
        return response()->json([
            'message' => 'El capítulo ya está desactivado',
            'chapter' => $chapter
        ]);
    }

    if ($validated['activate']) {
        $chapter->enabled = true;
    } else {
        $chapter->enabled = false;
    }

    $chapter->save();

    return response()->json([
        'message' => $validated['activate'] ? 'Capítulo publicado correctamente' : 'Capítulo archivado correctamente',
        'chapter' => $chapter
    ]);
}

}
