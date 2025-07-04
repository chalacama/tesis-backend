<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Course; 
use Illuminate\Http\JsonResponse;
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
            'course' => $course
        ]);
    }
    public function activateCourse(Request $request, $id)
    {
        $validated = $request->validate([
            'activate' => 'required|boolean',
        ]);

        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
        if ($validated['activate'] && $course->enabled) {
        return response()->json([
            'message' => 'El Curso ya está activado',
            'course' => $course
        ]);
    }
    if (!$validated['activate'] && !$course->enabled) {
        return response()->json([
            'message' => 'El Curso ya está desactivado',
            'module' => $course
        ]);
    }

        if ($validated['activate']) {
            $course->enabled = true;
            
        } else {
            $course->enabled = false;
            
        }

        $course->save();

        return response()->json([
            'message' => $validated['activate'] ? 'Curso publicado correctamente' : 'Curso archivado correctamente',
            'course' => $course
        ]);
    }
    public function softDeleteCourse($id)
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
    /* public function getAllCourses()
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
    } */

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
/**
     * Obtiene todos los cursos con su información agregada para una API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCourses(): JsonResponse
    {
        $courses = Course::query()
            // Cargar relaciones con condiciones
            ->with([
                'miniatures' => fn($query) => $query->where('enabled', true),
                'categories' => fn($query) => $query->where('enabled', true),
                'certified:id,course_id,is_certified', // Cargar solo campos necesarios
            ])
            // Contar módulos activos
            ->withCount(['modules as active_modules_count' => fn($query) => $query->where('enabled', true)])
            // Contar el total de comentarios y respuestas de una sola vez
            ->withCount(['allComments as total_comments_count' => fn($query) => $query->where('enabled', true)])
            // Contar guardados e inscripciones
            ->withCount(['savedCourses', 'registrations'])
            // Sumar todas las estrellas de calificación
            ->withSum('ratingCourses as total_stars', 'stars')
            // Obtener solo cursos que están habilitados
            ->where('enabled', true)
            ->get()
            // Transformar la colección para una respuesta de API limpia
            ->map(function ($course) {
                // El campo 'total_stars' puede ser null si no hay calificaciones, lo convertimos a 0.
                $course->total_stars = (int) $course->total_stars;

                // Simplificar la información del certificado
                $course->is_certified = $course->certified ? $course->certified->is_certified : false;

                // Formatear las categorías para que solo muestren id y nombre
                $course->categorias = $course->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name
                ]);

                // Eliminar las relaciones completas para no exponer datos innecesarios en la API
                unset($course->certified, $course->categories);

                return $course;
            });

        return response()->json(['courses' => $courses]);
    }

}
