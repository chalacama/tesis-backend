<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
class RegistrationController extends Controller
{
    use AuthorizesRequests;

/**
     * Registro SIN código (para cursos públicos y activos).
     * Ruta: POST /register/{course}/store
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('view', $course);

        // Reglas de negocio: debe estar ACTIVO y NO ser privado
        if (!$course->enabled || $course->private) {
            return response()->json([
                'ok'      => false,
                'message' => $course->private
                    ? 'Este curso es privado. Debes inscribirte con código.'
                    : 'El curso no está activo.',
            ], 403);
        }

        $user = $request->user();

        // Evitar doble inscripción
        if ($course->registrations()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Ya estás inscrito en este curso.',
            ], 409);
        }

        // Crear inscripción (con transacción por seguridad)
        $registration = DB::transaction(function () use ($user, $course) {
            return Registration::create([
                'user_id'   => $user->id,
                'course_id' => $course->id,
            ]);
        });

        return response()->json([
            'ok'      => true,
            'message' => 'Inscripción realizada con éxito.',
            'data'    => [
                'registration_id' => $registration->id,
                'course_id'       => $course->id,
                'user_id'         => $user->id,
            ],
        ], 201);
    }

    /**
     * Registro CON código (para cursos privados y activos).
     * Ruta: POST /code/store  (body: { "code": "ABC1234" })
     */
    public function code(Request $request)
    {
        // Validar input
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'], // el generateUniqueCode usa 7, pero dejamos margen
        ]);

        $code  = strtoupper(trim($validated['code']));
        $user  = $request->user();

        // Encontrar curso por código
        $course = Course::where('code', $code)->first();

        if (!$course) {
            // Código inválido
            throw ValidationException::withMessages([
                'code' => ['El código ingresado no es válido.'],
            ]);
        }

        // Opcional: $this->authorize('view', $course); // si tu política lo requiere
        $this->authorize('view', $course);

        // Reglas de negocio: debe estar ACTIVO y ser PRIVADO
        if (!$course->enabled) {
            return response()->json([
                'ok'      => false,
                'message' => 'El curso no está activo.',
            ], 403);
        }

        if (!$course->private) {
            return response()->json([
                'ok'      => false,
                'message' => 'Este curso es público y no requiere código.',
            ], 403);
        }

        // Evitar doble inscripción
        if ($course->registrations()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Ya estás inscrito en este curso.',
            ], 409);
        }

        // Crear inscripción
        $registration = DB::transaction(function () use ($user, $course) {
            return Registration::create([
                'user_id'   => $user->id,
                'course_id' => $course->id,
            ]);
        });

        return response()->json([
            'ok'      => true,
            'message' => 'Inscripción realizada con éxito mediante código.',
            'data'    => [
                'registration_id' => $registration->id,
                'course_id'       => $course->id,
                'user_id'         => $user->id,
            ],
        ], 201);
    }

}
