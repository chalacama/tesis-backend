<?php

namespace App\Http\Controllers;

use App\Mail\SendOTPCode;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Google\Client as GoogleClient;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * LOGIN UNIFICADO
     * Acepta credenciales tradicionales (email/password) O un token de Google (google_token).
     */
    public function login(Request $request): JsonResponse
    {
        // 1. SI RECIBE GOOGLE TOKEN
        if ($request->has('google_token')) {
            try {
                $client = new GoogleClient(['client_id' => config('services.google.client_id')]);
                $payload = $client->verifyIdToken($request->google_token);

                if (!$payload) {
                    return response()->json(['error' => 'Token de Google inválido'], 401);
                }

                $googleId = $payload['sub'];
                $email    = $payload['email'] ?? null;
                $name     = $payload['name'] ?? '';

                if (!$email) {
                    return response()->json(['error' => 'La cuenta de Google no tiene un correo válido.'], 422);
                }

                $user = User::where('email', $email)->first();

                // Si NO existe el usuario: No crearlo, indicar al frontend que debe registrarse
                if (!$user) {
                    return response()->json([
                        'is_registered' => false,
                        'email'         => $email,
                        'name'          => $name,
                    ], 200);
                }

                // Si SÍ existe el usuario: Actualizar google_id si es necesario y loguear
                if (empty($user->google_id)) {
                    $user->google_id = $googleId;
                    $user->save();
                }
                
                // Login exitoso vía Google
                return $this->generateLoginResponse($user, true);

            } catch (\Exception $e) {
                Log::error('Google Auth Error (Login): '.$e->getMessage());
                return response()->json([
                    'error' => 'La autenticación con Google falló: ' . $e->getMessage()
                ], 401);
            }
        }

        // 2. SI ES LOGIN TRADICIONAL
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $identifier = $request->identifier;
        $loginField = 'username'; // por defecto

        if (str_contains($identifier, '@')) {
            $loginField = 'email';
        } elseif (str_starts_with($identifier, '+593') && strlen($identifier) === 13) {
            $loginField = 'phone_number';
        } elseif (ctype_digit($identifier) && strlen($identifier) <= 10) {
            $loginField = 'cedula';
        }

        if (! Auth::attempt([$loginField => $identifier, 'password' => $request->password])) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        $user = User::where($loginField, $identifier)->firstOrFail();

        // Verificar que el correo esté verificado (solo para login tradicional)
        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Tu correo electrónico no ha sido verificado. Por favor, ingresa el código de verificación.',
                'action'  => 'verify_email',
                'email'   => $user->email,
            ], 403);
        }

        // Login exitoso vía Tradicional
        return $this->generateLoginResponse($user, false);
    }

    /**
     * REGISTRO UNIFICADO
     * Soporta registro tradicional O registro completado vía Google (google_token).
     * El usuario SIEMPRE debe proveer password, name, lastname y username.
     */
    public function register(Request $request): JsonResponse
    {
        // 1. Validaciones comunes
        $rules = [
            'name'     => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        // 2. Si NO viene google_token, el email es obligatorio
        if (!$request->has('google_token')) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email';
        }

        $request->validate($rules);

        $email = null;
        $googleId = null;
        $emailVerifiedAt = null;

        // 3. Procesamiento si viene con google_token
        if ($request->has('google_token')) {
            try {
                $client = new GoogleClient(['client_id' => config('services.google.client_id')]);
                $payload = $client->verifyIdToken($request->google_token);

                if (!$payload) {
                    return response()->json(['error' => 'Token de Google inválido'], 401);
                }

                $email = $payload['email'] ?? null;
                $googleId = $payload['sub'];
                $emailVerifiedAt = now(); // Google ya verificó el correo

                if (!$email) {
                    return response()->json(['error' => 'La cuenta de Google no tiene un correo válido.'], 422);
                }

                // Verificar que el correo extraído de Google no esté en uso
                if (User::where('email', $email)->exists()) {
                    return response()->json(['message' => 'El correo electrónico ya está registrado.'], 422);
                }

            } catch (\Exception $e) {
                Log::error('Google Auth Error (Register): '.$e->getMessage());
                return response()->json([
                    'error' => 'La validación del token de Google falló: ' . $e->getMessage()
                ], 401);
            }
        } else {
            // Si es registro tradicional, usamos el email del request
            $email = $request->email;
        }

        // 4. Crear el usuario
        $user = User::create([
            'google_id' => $googleId,
            'name'      => $request->name,
            'lastname'  => $request->lastname,
            'username'  => $request->username,
            'email'     => $email,
            'password'  => Hash::make($request->password), // SIEMPRE usa la que provee el usuario
            'email_verified_at' => $emailVerifiedAt,
        ]);

        $user->assignRole('student');

        $response = [
            'message'             => 'Usuario registrado exitosamente.',
            'user'                => $user,
            'role'                => 'student',
            'can_update_username' => true,
        ];

        // 5. Si es tradicional, enviar OTP. Si es Google, no se envía nada.
        if (!$request->has('google_token')) {
            VerificationCode::active($user->id, 'email_verification', 'email')
                ->update(['used_at' => now()]);

            $plainCode = VerificationCode::generateCode(6);

            $verificationCode = VerificationCode::create([
                'user_id'    => $user->id,
                'code'       => Hash::make($plainCode),
                'type'       => 'email_verification',
                'channel'    => 'email',
                'expires_at' => now()->addMinutes(15),
            ]);

            Mail::to($user->email)->send(new SendOTPCode($user, $plainCode, 'email_verification'));

            $response['message'] = 'Usuario registrado exitosamente. Se ha enviado un código de verificación a tu correo.';
            $response['verification'] = [
                'code_sent'          => true,
                'expires_in_seconds' => now()->diffInSeconds($verificationCode->expires_at),
            ];
        }

        return response()->json($response, 201);
    }

    /**
     * LOGOUT (CIERRE DE SESIÓN)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente.']);
    }

    /**
     * Helper para generar la respuesta común de un inicio de sesión exitoso.
     */
    private function generateLoginResponse(User $user, bool $isGoogleLogin): JsonResponse
    {
        $token = $user->createToken('auth_token_login')->plainTextToken;
        $expiresInMinutes = config('sanctum.expiration');
        $expiresAt = now()->addMinutes($expiresInMinutes);

        $canUpdateUsername = false;
        if (is_null($user->username_at)) {
            $canUpdateUsername = true;
        } else {
            $usernameAt = $user->username_at instanceof Carbon
                ? $user->username_at
                : Carbon::parse($user->username_at);
            if (now()->greaterThanOrEqualTo($usernameAt->copy()->addMonths(3))) {
                $canUpdateUsername = true;
            }
        }

        $user->makeHidden('roles');

        $responseData = [
            'message'                    => 'Inicio de sesión exitoso.',
            'access_token'               => $token,
            'token_type'                 => 'Bearer',
            'expires_at'                 => $expiresAt->toIso8601String(),
            'user'                       => $user,
            'role'                       => $user->getRoleNames()[0] ?? 'student',
            'can_update_username'        => $canUpdateUsername,
            'has_user_category_interest' => $user->hasCategoryInterest(),
        ];

        // Requerido por el usuario: añadir flag is_registered = true si es vía Google.
        // De igual forma lo podemos mandar siempre para consistencia o solo si es Google.
        if ($isGoogleLogin) {
            $responseData['is_registered'] = true;
        }

        return response()->json($responseData);
    }
}
