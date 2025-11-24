<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
class UserController extends Controller
{
    use AuthorizesRequests;

    public function update(Request $request)
    {
        $user = $request->user();

        $this->authorize('update', $user);

        // Validación para username
        $validatedData = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
        ], [
            'username.regex' => 'El usuario solo puede contener letras minúsculas, números, puntos (.) y guiones bajos (_). Sin espacios ni mayúsculas.',
            'username.unique' => 'Este nombre de usuario ya está ocupado.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
        ]);

        $newUsername = $validatedData['username'];

        // Si es el mismo username, no tiene sentido guardar ni tocar username_at
        if ($newUsername === $user->username) {
            return response()->json([
                'message'  => 'No se realizaron cambios en el nombre de usuario.',
                'username' => $user->username,
                'username_at' => optional($user->username_at)->toDateTimeString(),
            ]);
        }

        // Regla de tiempo:
        // - Si username_at es null -> primer cambio permitido siempre
        // - Si NO es null -> debe haber pasado al menos 3 meses
        if ($user->username_at !== null) {
            $limiteTresMeses = $user->username_at->copy()->addMonths(3);

            if (now()->lt($limiteTresMeses)) {
                return response()->json([
                    'message' => 'Solo puedes cambiar tu nombre de usuario cada 3 meses.',
                    'next_allowed_change_at' => $limiteTresMeses->toDateTimeString(),
                ], 422);
            }
        }

        // Aquí ya está permitido el cambio
        $user->username    = $newUsername;
        $user->username_at = now(); // registramos la fecha del ÚLTIMO cambio
        $user->save();

        return response()->json([
            'message'       => 'User actualizado con éxito',
            'username'      => $user->username,
            'username_at'   => $user->username_at->toDateTimeString(),
        ]);
    }

    public function validateUsername(Request $request)
    {
        $user = $request->user();

        $this->authorize('update', $user);

        // Validar formato (sin verificar unicidad aún)
        $validator = \Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._]+$/',
            ],
        ], [
            'username.regex' => 'El usuario solo puede contener letras minúsculas, números, puntos (.) y guiones bajos (_). Sin espacios ni mayúsculas.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validación fallida.',
                'errors' => $validator->errors(),
                'is_available' => false,
            ], 422);
        }

        $username = $request->input('username');

        // Si es el mismo que el actual, lo consideramos disponible
        if ($username === $user->username) {
            return response()->json([
                'message' => 'El nombre de usuario es igual al actual.',
                'is_available' => false,
            ]);
        }

        // Comprobar existencia en la base de datos
        $exists = User::where('username', $username)->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este nombre de usuario ya está ocupado.',
                'is_available' => false,
            ]);
        }

        return response()->json([
            'message' => 'El nombre de usuario es válido y está disponible.',
            'is_available' => true,
        ]);
    }
}
