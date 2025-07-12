<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTutorCourseRequest;
use App\Http\Resources\TutorCourseResource;
use App\Models\TutorCourse;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
class TutorCourseController extends Controller
{
public function store(CreateTutorCourseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::find($validated['user_id']);

        if (!$user || !$user->hasRole('tutor')) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario especificado no tiene el rol de tutor',
                'errors' => [
                    'user_id' => ['El usuario debe tener el rol de tutor para ser asignado a un curso']
                ]
            ], 422);
        }

        $exists = TutorCourse::where('user_id', $validated['user_id'])
            ->where('course_id', $validated['course_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una asignación para este tutor y curso',
                'errors' => [
                    'assignment' => ['El tutor ya está asignado a este curso']
                ]
            ], 409);
        }

        try {
            DB::beginTransaction();

            $tutorCourse = TutorCourse::create($validated);
            $tutorCourse->load(['user:id,name,email', 'course:id,title,description']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tutor asignado al curso exitosamente',
                'data' => [
                    'id' => $tutorCourse->id,
                    'enabled' => $tutorCourse->enabled,
                    'tutor' => $tutorCourse->user->only(['id', 'name', 'email']),
                    'course' => $tutorCourse->course->only(['id', 'title', 'description']),
                    'created_at' => $tutorCourse->created_at->toISOString(),
                    'updated_at' => $tutorCourse->updated_at->toISOString(),
                ]
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al asignar tutor al curso', ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Ha ocurrido un error inesperado'
            ], 500);
        }
    }
   
private function delete($id)
{
    $tutorCourse = TutorCourse::find($id);
    if (!$tutorCourse) {
        return response()->json(['message' => 'Asignación de tutor a curso no encontrada'], 404);
    }
    $tutorCourse->delete();
    return response()->json(['message' => 'Asignación de tutor a curso eliminada correctamente']);
}

public function change(CreateTutorCourseRequest $request): JsonResponse
{
    $validated = $request->validated();

    $newTutor = User::find($validated['user_id']);

    if (!$newTutor || !$newTutor->hasRole('tutor')) {
        return response()->json([
            'success' => false,
            'message' => 'El usuario especificado no tiene el rol de tutor',
            'errors' => [
                'user_id' => ['El usuario debe tener el rol de tutor para ser asignado a un curso']
            ]
        ], 422);
    }

    try {
        DB::beginTransaction();

        $existingAssignment = TutorCourse::where('course_id', $validated['course_id'])->first();

        $oldTutorName = null;

        if ($existingAssignment) {
            $oldTutorName = $existingAssignment->user->name;
            
            // Actualizamos el registro existente
            $existingAssignment->update([
                'user_id' => $validated['user_id'],
                'enabled' => $validated['enabled'],
            ]);

            $tutorCourse = $existingAssignment;
        } else {
            // Creamos nueva asignación si no existe
            $tutorCourse = TutorCourse::create($validated);
        }

        $tutorCourse->load(['user:id,name,email', 'course:id,title,description']);

        DB::commit();

        $newTutorName = $tutorCourse->user->name;
        $courseTitle = $tutorCourse->course->title;

        $message = $oldTutorName
            ? "El tutor {$oldTutorName} fue reemplazado por {$newTutorName} en el curso '{$courseTitle}'"
            : "Tutor {$newTutorName} asignado al curso '{$courseTitle}' exitosamente";

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $tutorCourse->id,
                'enabled' => $tutorCourse->enabled,
                'tutor' => $tutorCourse->user->only(['id', 'name', 'email']),
                'course' => $tutorCourse->course->only(['id', 'title', 'description']),
                'created_at' => $tutorCourse->created_at->toISOString(),
                'updated_at' => $tutorCourse->updated_at->toISOString(),
            ]
        ], $oldTutorName ? 200 : 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Error al cambiar tutor del curso', ['error' => $e]);

        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor',
            'error' => config('app.debug') ? $e->getMessage() : 'Ha ocurrido un error inesperado'
        ], 500);
    }
}
}
