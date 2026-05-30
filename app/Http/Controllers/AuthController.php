<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Para registrar errores
use Illuminate\Support\Str; // Para generar cadenas aleatorias
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;
use Google\Client as GoogleClient;
use Carbon\Carbon;
class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
{
    // 1. Validación estricta de los datos de entrada
    $request->validate([
        'name' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => ['required', 'confirmed', Password::defaults()],
    ]);

    // 2. Creación del usuario
    $user = User::create([
        'name'                => $request->name,
        'lastname'            => $request->lastname,
        'username'            => $request->username,
        'email'               => $request->email,
        'password'            => Hash::make($request->password),
        'register_method' => 'email',
        // 'username_at' => null, // opcional, por defecto ya es null
    ]);

    // 3. Asignar rol por defecto
    $user->assignRole('student');

    // 4. Enviar correo de verificación
    $user->sendEmailVerificationNotification();

    // 5. Lógica de can_update_username
    // Recién creado, username_at es null → puede cambiar
    $canUpdateUsername = true;

    return response()->json([
        'message'             => 'Usuario registrado exitosamente. Por favor, verifica tu correo electrónico.',
        'user'                => $user,
        'role'                => $user->getRoleNames()[0] ?? 'student',
        'can_update_username' => $canUpdateUsername,
    ], 201);
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
    if (! Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Credenciales incorrectas.'], 401);
    }

    // 3. Usuario autenticado
    $user = User::where('email', $request->email)->firstOrFail();

    // Verificar si el correo está verificado
    if (! $user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Por favor, verifica tu correo electrónico antes de iniciar sesión.'
        ], 403);
    }

    // 4. Revocar tokens antiguos y crear uno nuevo
    // 4. Crear token para ESTA sesión sin eliminar los anteriores
    $token = $user->createToken('auth_token_login')->plainTextToken;
    $expiresInMinutes = config('sanctum.expiration'); // 1440
    $expiresAt = now()->addMinutes($expiresInMinutes);
    // Cargar relaciones
    $user->load(['userInformation', 'educationalUser']);

    // 5. Calcular can_update_username (misma lógica que en Google)
    $canUpdateUsername = false;

    if (is_null($user->username_at)) {
        // Nunca ha cambiado -> primer cambio permitido
        $canUpdateUsername = true;
    } else {
        $usernameAt = $user->username_at instanceof Carbon
            ? $user->username_at
            : Carbon::parse($user->username_at);

        $limit = $usernameAt->copy()->addMonths(3);

        if (now()->greaterThanOrEqualTo($limit)) {
            $canUpdateUsername = true;
        }
    }

    return response()->json([
        'message'                   => 'Inicio de sesión exitoso.',
        'access_token'              => $token,
        'token_type'                => 'Bearer',
        'expires_at'                => $expiresAt->toIso8601String(), // 👈 NUEVO
        'user'                      => $user,
        'role'                      => $user->getRoleNames()[0] ?? 'student',
        'can_update_username'       => $canUpdateUsername, // 👈 AQUÍ, justo después de role

        // NUEVOS CAMPOS
        'has_user_information'       => $user->hasUserInformation(),
        'has_educational_user'       => $user->hasEducationalUser(),
        'has_user_category_interest' => $user->hasCategoryInterest(),
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
            // 1. Configurar el cliente de Google para verificar el ID TOKEN (JWT)
            $client = new GoogleClient(['client_id' => config('services.google.client_id')]);
            
            // Verificamos el token que llega desde Angular (que empieza por eyJ...)
            $payload = $client->verifyIdToken($request->token);

            if (!$payload) {
                return response()->json(['error' => 'Token de Google inválido'], 401);
            }

            // 2. Extraer datos del payload del JWT verificado
            $googleId = $payload['sub'];
            $email    = $payload['email'] ?? null;
            $name     = $payload['name'] ?? '';
            $avatar   = $payload['picture'] ?? null;

            if (!$email) {
                return response()->json([
                    'error' => 'La cuenta de Google no tiene un correo válido.'
                ], 422);
            }

            // Separar nombre y apellido
            $fullName = explode(' ', $name, 2);

            // 3. Buscar si ya existe un usuario con ese email
            $user = User::where('email', $email)->first();
            $isNewUser = false;

            if (!$user) {
                // PRIMERA VEZ: crear usuario con username único y username_at = null
                $user = new User();
                $user->google_id           = $googleId;
                $user->email               = $email;
                $user->name                = $fullName[0] ?? '';
                $user->lastname            = $fullName[1] ?? '';
                $user->username            = Str::slug($name) . '_' . uniqid();
                $user->username_at         = null; // primer cambio libre
                $user->register_method = 'google';
                $user->profile_picture_url = $avatar;
                $user->email_verified_at   = now();
                $user->save();

                $isNewUser = true;

                // Rol inicial
                $user->assignRole('student');
            } else {
                // YA EXISTE: NO tocar username ni username_at
                $user->google_id = $googleId;

                // Actualizar datos suaves
                $user->name     = $fullName[0] ?? $user->name;
                $user->lastname = $fullName[1] ?? $user->lastname;

                if (!$user->register_methodod) {
                    $user->register_method = 'google';
                }

                if ($avatar) {
                    $user->profile_picture_url = $avatar;
                }

                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                }

                $user->save();
            }

            // 4. Gestión de Tokens de Laravel Sanctum
            // Simplemente crea un token nuevo
            $token = $user->createToken('auth_token_google')->plainTextToken;
            $expiresInMinutes = config('sanctum.expiration');
            $expiresAt = now()->addMinutes($expiresInMinutes);
            // 5. Calcular si puede actualizar el username (booleano)
            $canUpdateUsername = false;

            if (is_null($user->username_at)) {
                // Nunca ha cambiado → primer cambio permitido
                $canUpdateUsername = true;
            } else {
                // Deben haber pasado 3 meses desde el último cambio
                $usernameAt = $user->username_at instanceof Carbon
                    ? $user->username_at
                    : Carbon::parse($user->username_at);

                $limit = $usernameAt->copy()->addMonths(3);

                if (now()->greaterThanOrEqualTo($limit)) {
                    $canUpdateUsername = true;
                }
            }

            // Cargar relaciones
            $user->load(['userInformation', 'educationalUser']);

            return response()->json([
                'access_token'               => $token,
                'token_type'                 => 'Bearer',
                'user'                       => $user,
                'expires_at'                 => $expiresAt->toIso8601String(), // 👈
                'role'                       => $user->getRoleNames()[0] ?? 'student',
                'can_update_username'        => $canUpdateUsername, // 👈 AQUÍ EL BOOLEANO
                'has_user_information'       => $user->hasUserInformation(),
                'has_educational_user'       => $user->hasEducationalUser(),
                'has_user_category_interest' => $user->hasCategoryInterest(),
            ]);

        } catch (\Exception $e) {
            Log::error('Google Auth Error: '.$e->getMessage());
            return response()->json([
                'error' => 'La autenticación con Google falló: ' . $e->getMessage()
            ], 401);
        }
    }
}
