<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log; // Para registrar errores
use Illuminate\Support\Str; // Para generar cadenas aleatorias
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\JsonResponse;
class AuthController extends Controller
{
    /* public function handleGoogleCallback(Request $request)
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
    } */
    /**
     * REGISTRO TRADICIONAL
     * Crea un nuevo usuario a partir de un formulario.
     */
    public function register(Request $request): JsonResponse
    {
        // 1. Validación estricta de los datos de entrada
        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()], // 'confirmed' busca un campo 'password_confirmation'
        ]);

        // 2. Creación del usuario
        $user = User::create([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password), // ¡CRÍTICO! Hashear siempre la contraseña
            'registration_method' => 'email', // Método de registro tradicional
        ]);

        // 3. Asignar rol por defecto (si usas spatie/laravel-permission)
        $user->assignRole('student');

        // 4. Crear un token para que el usuario inicie sesión inmediatamente
        $token = $user->createToken('auth_token_register')->plainTextToken;

        // 5. Devolver la respuesta
        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201); // 201 Created
    }

    /**
     * LOGIN TRADICIONAL
     * Autentica a un usuario y le devuelve un token.
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validación de las credenciales
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Intentar autenticar al usuario
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Si la autenticación falla, devolver error
            return response()->json(['message' => 'Credenciales incorrectas.'], 401); // 401 Unauthorized
        }

        // 3. Si la autenticación es exitosa, obtener el usuario
        $user = User::where('email', $request->email)->firstOrFail();

        // 4. Revocar tokens antiguos y crear uno nuevo para mayor seguridad
        $user->tokens()->delete();
        $token = $user->createToken('auth_token_login')->plainTextToken;

        // 5. Devolver la respuesta
        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * LOGOUT (CIERRE DE SESIÓN)
     * Invalida el token actual del usuario. Funciona para AMBOS métodos.
     */
    public function logout(Request $request): JsonResponse
    {
        // El middleware 'auth:sanctum' ya ha verificado que el usuario está autenticado.
        // Revocamos únicamente el token que se usó para hacer esta petición.
        // Esto permite que el usuario siga logueado en otros dispositivos.
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada exitosamente.']);
    }

    /**
     * LOGIN/REGISTRO CON GOOGLE
     * Gestiona la autenticación a través de Google.
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);

            $fullName = explode(' ', $googleUser->name, 2);

            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'google_id' => $googleUser->id,
                    'name' => $fullName[0] ?? '',
                    'lastname' => $fullName[1] ?? '',
                    'username' => $googleUser->nickname ?? Str::slug($googleUser->name) . '_' . uniqid(),
                    'registration_method' => 'google',
                    'email_verified_at' => now(),
                ]
            );

            if ($user->wasRecentlyCreated) {
                $user->assignRole('student');
            }
            
            // Revocar tokens antiguos para este usuario y crear uno nuevo
            $user->tokens()->where('name', 'like', 'auth_token_%')->delete();
            $token = $user->createToken('auth_token_google')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage());
            return response()->json(['error' => 'La autenticación con Google falló.'], 401);
        }
    }
}
