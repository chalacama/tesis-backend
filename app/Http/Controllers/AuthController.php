<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log; // Para registrar errores
use Illuminate\Support\Str; // Para generar cadenas aleatorias
use Laravel\Socialite\Facades\Socialite;
class AuthController extends Controller
{
    public function handleGoogleCallback(Request $request)
    {
        // 1. Validar que el frontend envió un token
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            // 2. Verificar el token de Google usando Socialite en modo "stateless"
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);

            // Separar el nombre y apellido del nombre completo de Google
            $fullName = explode(' ', $googleUser->name, 2);
            $firstName = $fullName[0];
            $lastName = isset($fullName[1]) ? $fullName[1] : ''; // Apellido es opcional

            // 3. Buscar o crear el usuario en tu base de datos
            $user = User::updateOrCreate(
                [
                    // Criterio de búsqueda: el email que nos da Google
                    'email' => $googleUser->email,
                ],
                [
                    // Datos para crear o actualizar
                    'google_id' => $googleUser->id,
                    'name' => $firstName,
                    'lastname' => $lastName,
                    // Creamos un username único por si acaso ya existe
                    'username' => $googleUser->nickname ?? Str::slug($googleUser->name) . '_' . uniqid(),
                    'registration_method' => 'google',
                    'email_verified_at' => now(), // El email de Google ya está verificado
                ]
            );

            // 4. Asignar rol de "student" si es un usuario nuevo
            if ($user->wasRecentlyCreated) {
                $user->assignRole('student');
            }

            // 5. Crear un token de Sanctum para el usuario
            $token = $user->createToken('auth_token_google')->plainTextToken;

            // 6. Devolver la respuesta al frontend
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);

        } catch (\Exception $e) {
           // Esto te dará el mensaje exacto del error, el archivo y la línea.
    Log::error('Error de autenticación con Google: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    
    // Devuelve un error más específico si estás en modo de depuración
    return response()->json([
        'error' => 'La autenticación con Google falló.',
        'message' => config('app.debug') ? $e->getMessage() : 'Ocurrió un error inesperado.'
    ], 401);
        }
    }
}
