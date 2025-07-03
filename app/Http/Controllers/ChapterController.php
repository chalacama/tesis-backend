<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
class ChapterController extends Controller
{
    public function createChapter(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'order' => 'required|integer',
        'module_id' => 'required|integer',
    ]);

    $chapter = Chapter::create($validated);

    return response()->json([
        'message' => 'Capítulo creado correctamente',
        'chapter' => $chapter
    ]);
}
public function updateChapter(Request $request, $id)
{
    $validated = $request->validate([
        'title' => 'required|string',
        'description' => 'nullable|string',
        'order' => 'required|integer',
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

public function softDeleteChapter($id)
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
}
