<?php

namespace App\Http\Controllers;

use App\Mail\SendOTPCode;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VerificationController extends Controller
{
    use AuthorizesRequests;
    public function passwordUpdate(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('update', $user);

        $validated = $request->validate([
            'password_old' => ['required', 'string', 'min:6', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'password_confirmation' => ['required', 'string', 'min:6', 'max:255']
        ]);

        $oldPassword = $request->old_password;
        if (!Hash::check($oldPassword, $user->password)) {
            return response()->json([
                'message' => 'La contraseña anterior no es correcta',
                'logout_forced' => false
            ], 422);
        }

        if ($request->password !== $request->password_confirmation) {
            return response()->json([
                'message' => 'Las contraseñas no coinciden',
                'logout_forced' => false
            ], 422);
        }

        if ($request->password === $user->password) {
            return response()->json([
                'message' => 'La contraseña nueva no puede ser igual a la contraseña anterior',
                'logout_forced' => false
            ], 422);
        }

        $user->password = bcrypt($validated['password']);
        $user->save();
        //cerrar sesiones abiertas en otros navegadores
        $user->tokens()->delete();
        return response()->json([
            'message' => 'Contraseña actualizada correctamente',
            'logout_forced' => true
        ]);
    }

    public function usernameUpdate(Request $request): JsonResponse
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
                'regex:/^[a-z0-9._-]+$/',
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
        // - Si NO es null -> debe haber pasado al menos 6 meses
        if ($user->username_at !== null) {
            $limiteTresMeses = $user->username_at->copy()->addMonths(6);

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

    public function emailUpdate(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('update', $user);

        // Validación para email
        $validatedData = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $newEmail = $validatedData['email'];

        // Si es el mismo email y el usuario esta verificado, no hacer nada
        if ($newEmail === $user->email && $user->email_verified_at !== null) {
            return response()->json([
                'message' => 'No se realizaron cambios en el correo electrónico.',
                'email' => $user->email,
                'email_verified_at' => optional($user->email_verified_at)->toDateTimeString(),
            ]);
        }
        
        // 1. Marcar como no verificado
        $user->email_verified_at = null;
        $user->save();

        // 2. Desencadenar flujo de verificación (enviar código nuevo)


        // 3. Retornar respuesta estándar
        return response()->json([
            'message' => 'Se ha enviado un código de verificación al correo electrónico.' ,
            'email' => $user->email,
            'email_verified_at' => null,
        ]);
    
    }

    public function phoneUpdate(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('update', $user);

        // Validación para phone
        $validatedData = $request->validate([
            'phone_number' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^\+593[0-9]{9}$/',
                Rule::unique('users', 'phone_number')->ignore($user->id),
            ],
        ]);

        $newPhone = $validatedData['phone_number'];

        // Si es el mismo phone y el usuario esta verificado, no hacer nada
        if ($newPhone === $user->phone_number && $user->phone_verified_at !== null) {
            return response()->json([
                'message' => 'No se realizaron cambios en el teléfono.',
                'phone_number' => $user->phone_number,
                'phone_verified_at' => optional($user->phone_verified_at)->toDateTimeString(),
            ]);
        }

        // 1. Marcar como no verificado
        $user->phone_verified_at = null;
        $user->save();

        // 2. Desencadenar flujo de verificación (enviar código nuevo)

        
        // 3. Retornar respuesta estándar
        return response()->json([
            'message' => 'Se ha enviado un código de verificación al teléfono.' ,
            'phone_number' => $user->phone_number,
            'phone_verified_at' => null,
        ]);
    
    }

    public function cedulaUpdate(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('update', $user);

        // Validación para cedula
        $validatedData = $request->validate([
            'cedula' => [
                'required',
                'string',
                'min:10',
                'max:10',
                'regex:/^[0-9]{10}$/',
                Rule::unique('users', 'cedula')->ignore($user->id),
            ],
        ]);

        $newCedula = $validatedData['cedula'];

        // Si es el mismo cedula y el usuario esta verificado, no hacer nada
        if ($newCedula === $user->cedula && $user->cedula_verified_at !== null) {
            return response()->json([
                'message' => 'No se realizaron cambios en la cedula.',
                'cedula' => $user->cedula,
                'cedula_verified_at' => optional($user->cedula_verified_at)->toDateTimeString(),
            ]);
        }

        
        // Retornar respuesta estándar
        return response()->json([
            'message' => 'Se ha enviado un código de verificación a la cedula.' ,
            'cedula' => $user->cedula,
            'cedula_verified_at' => null,
        ]);
    
    }

}
