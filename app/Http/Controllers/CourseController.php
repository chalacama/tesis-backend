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
    public function getAllCourses()
    {
        $courses = Course::with([
            // Traer solo miniaturas habilitadas
            'miniatures' => function ($query) {
                $query->where('enabled', true);
            },
            // Traer categorías con su nombre
            'categories' => function ($query) {
                $query->where('enabled', true);
            },
            
        ])
        // Contar módulos activos
        ->withCount(['modules as active_modules_count' => function ($query) {
            $query->where('enabled', true);
        }])
        // Contar estrellas (sumar stars de rating_courses)
        ->withSum('ratingCourses as total_stars', 'stars')
        // Contar guardados
        ->withCount('savedCourses')
        // Contar inscripciones
        ->withCount('registrations')
        // Contar comentarios
        ->withCount(['comments' => function ($query) {
            $query->where('enabled', true);
        }])
        // Contar respuestas a comentarios (replyComments en Comment) solo si están activos
        ->withCount(['comments as reply_comments_count' => function ($query) {
            $query->join('reply_comments', 'comments.id', '=', 'reply_comments.comment_id')
              ->where('reply_comments.enabled', true);
        }])
        // Saber si tiene certificado
        ->with(['certified:id,course_id,is_certified'])
        ->get()
        // Sumar comentarios y respuestas
        ->map(function ($course) {
            $course->total_comments = $course->comments_count + $course->reply_comments_count;
            // Mostrar solo el campo is_certified si existe
            $course->is_certified = $course->certified ? $course->certified->is_certified : false;
            // Mostrar id y nombre de las categorías
    $course->categorias = $course->categories->map(function($cat) {
        return [
            'id' => $cat->id,
            'name' => $cat->name
        ];
    });
            // Eliminar relaciones innecesarias para respuesta más limpia
            unset($course->certified, $course->categories,$course->comments_count,$course->reply_comments_count);
            return $course;
        });

        return response()->json([
            'courses' => $courses
        ]);
    }

    public function getCourseDetail($id)
    {
        $course = Course::with([
            'categories',            // categorías del curso
            'tutors',                // tutores del curso
            'modules.chapters.learningContent', 
            'modules.chapters.questions.typeQuestion', 
            'modules.chapters.questions.answers',      
        ])->find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        return response()->json([
            'course' => $course
        ]);
    }


}
