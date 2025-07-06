<?php

namespace App\Http\Controllers;

use App\Models\TutorCourse;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
class TutorCourseController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        try {
            // Validar los datos de entrada
            $validatedData = $request->validate([
                'enabled' => 'required|boolean',
                'course_id' => 'required|integer|exists:courses,id',
                'user_id' => 'required|integer|exists:users,id',
            ], [
                'enabled.required' => 'El campo enabled es obligatorio',
                'enabled.boolean' => 'El campo enabled debe ser verdadero o falso',
                'course_id.required' => 'El ID del curso es obligatorio',
                'course_id.integer' => 'El ID del curso debe ser un número entero',
                'course_id.exists' => 'El curso especificado no existe',
                'user_id.required' => 'El ID del usuario es obligatorio',
                'user_id.integer' => 'El ID del usuario debe ser un número entero',
                'user_id.exists' => 'El usuario especificado no existe',
            ]);

            // Verificar si el usuario tiene el rol de tutor
            $user = User::findOrFail($validatedData['user_id']);
            if (!$user->hasRole('tutor')) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario especificado no tiene el rol de tutor',
                    'errors' => [
                        'user_id' => ['El usuario debe tener el rol de tutor para ser asignado a un curso']
                    ]
                ], 422);
            }

            // Verificar si ya existe una asignación para este tutor y curso
            $existingAssignment = TutorCourse::where('user_id', $validatedData['user_id'])
                ->where('course_id', $validatedData['course_id'])
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una asignación para este tutor y curso',
                    'errors' => [
                        'assignment' => ['El tutor ya está asignado a este curso']
                    ]
                ], 409);
            }

            // Verificar que el curso existe y está disponible
            $course = Course::findOrFail($validatedData['course_id']);

            // Crear la asignación usando transacción para garantizar consistencia
            DB::beginTransaction();

            $tutorCourse = TutorCourse::create([
                'enabled' => $validatedData['enabled'],
                'course_id' => $validatedData['course_id'],
                'user_id' => $validatedData['user_id'],
            ]);

            // Cargar las relaciones para la respuesta
            $tutorCourse->load(['user:id,name,email', 'course:id,title,description']);

            DB::commit();

            // Log de la acción
            Log::info('TutorCourse creado exitosamente', [
                'tutor_course_id' => $tutorCourse->id,
                'user_id' => $validatedData['user_id'],
                'course_id' => $validatedData['course_id'],
                'enabled' => $validatedData['enabled']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tutor asignado al curso exitosamente',
                'data' => [
                    'id' => $tutorCourse->id,
                    'enabled' => $tutorCourse->enabled,
                    'tutor' => [
                        'id' => $tutorCourse->user->id,
                        'name' => $tutorCourse->user->name,
                        'email' => $tutorCourse->user->email,
                    ],
                    'course' => [
                        'id' => $tutorCourse->course->id,
                        'title' => $tutorCourse->course->title,
                        'description' => $tutorCourse->course->description,
                    ],
                    'created_at' => $tutorCourse->created_at->toISOString(),
                    'updated_at' => $tutorCourse->updated_at->toISOString(),
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear TutorCourse', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Ha ocurrido un error inesperado'
            ], 500);
        }
    }
}
