<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Course; 
class CourseController extends Controller
{
    public function createCourse(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
// now()
        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            // Los demás campos toman el valor por defecto de la migración
        ]);

        return response()->json([
            'message' => 'Curso creado correctamente',
            'course' => $course
        ], 201);
    }
    public function updateCourse(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $course->update($validated);

        // Solo retorna los campos necesarios
        return response()->json([
            'message' => 'Curso actualizado correctamente',
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'created_at' => $course->created_at,
                'updated_at' => $course->updated_at,
            ]
        ]);
    }
    public function publishCourse(Request $request, $id)
    {
        $validated = $request->validate([
            'publish' => 'required|boolean',
        ]);

        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        if ($validated['publish']) {
            $course->enabled = true;
            $course->published_at = now();
        } else {
            $course->enabled = false;
            $course->archived_at = now();
        }

        $course->save();

        return response()->json([
            'message' => $validated['publish'] ? 'Curso publicado correctamente' : 'Curso archivado correctamente',
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'enabled' => $course->enabled,
                'archived_at' => $course->archived_at,
                'published_at' => $course->published_at,
                'created_at' => $course->created_at,
                'updated_at' => $course->updated_at,
            ]
        ]);
    }
    public function deleteCourse($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $course->delete();

        return response()->json([
            'message' => 'Curso enviado a papelería correctamente'
        ]);
    }


}
