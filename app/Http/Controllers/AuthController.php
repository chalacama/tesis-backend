<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Google\Client as GoogleClient;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class AuthController extends Controller
{
    use AuthorizesRequests;
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

        // Verificar que el identificador usado esté verificado
        if ($loginField === 'email' && is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Tu correo electrónico no ha sido verificado. Por favor, inicie sesión con su usuario para verificarlo.',
                'action'  => 'verify_email',
                'email'   => $user->email,
            ], 403);
        }

        if ($loginField === 'phone_number' && is_null($user->phone_verified_at)) {
            return response()->json([
                'message' => 'Tu número de teléfono no ha sido verificado. Por favor, inicie sesión con su usuario para verificarlo.',
                'action'  => 'verify_phone',
                'phone_number' => $user->phone_number,
            ], 403);
        }

        if ($loginField === 'cedula' && is_null($user->cedula_verified_at)) {
            return response()->json([
                'message' => 'Tu cédula no ha sido verificada. Por favor, inicie sesión con su usuario para verificarla.',
                'action'  => 'verify_cedula',
                'cedula'  => $user->cedula,
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
            'name'         => 'required|string|max:255',
            'lastname'     => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username',
            'password'     => ['required', 'confirmed', Password::defaults()],
            'phone_number' => 'nullable|string|max:13|unique:users,phone_number',
            'cedula'       => 'nullable|string|max:10|unique:users,cedula',
            'birthdate'    => 'required_with:cedula|date_format:Y-m-d',
        ];

        // 2. Si NO viene google_token, el email es obligatorio
        if (!$request->has('google_token')) {
            $rules['email'] = 'nullable|string|email|max:255|unique:users,email';
        }

        $request->validate($rules);

        $email = null;
        $googleId = null;
        $emailVerifiedAt = null;

        // 3. Validación de Cédula mediante API externa
        $cedulaVerifiedAt = null;
        $sexo = null;

        if ($request->filled('cedula')) {
            try {
                $response = Http::asForm()->post('https://si.secap.gob.ec/sisecap/logeo_web/json/busca_persona_registro_civil.php', [
                    'documento' => $request->cedula,
                    'tipo'      => '1',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // La API puede devolver el objeto directo o dentro de un array
                    $person = is_array($data) && isset($data[0]) ? $data[0] : $data;
                    
                    if (isset($person['nombres']) && isset($person['apellidos'])) {
                        // Limpiar y normalizar nombres y apellidos a mayúsculas sin espacios extra
                        $apiNombres = preg_replace('/\s+/', ' ', trim(strtoupper($person['nombres'])));
                        $apiApellidos = preg_replace('/\s+/', ' ', trim(strtoupper($person['apellidos'])));
                        $reqName = preg_replace('/\s+/', ' ', trim(strtoupper($request->name)));
                        $reqLastname = preg_replace('/\s+/', ' ', trim(strtoupper($request->lastname)));
                        
                        $apiBirthdate = null;
                        if (isset($person['fechaNacimiento'])) {
                            try {
                                $apiBirthdate = Carbon::createFromFormat('d/m/Y', $person['fechaNacimiento'])->format('Y-m-d');
                            } catch (\Exception $e) {
                                // Por si la API devuelve otro formato
                                $apiBirthdate = date('Y-m-d', strtotime(str_replace('/', '-', $person['fechaNacimiento'])));
                            }
                        }

                        if ($apiNombres !== $reqName || $apiApellidos !== $reqLastname || ($apiBirthdate && $apiBirthdate !== $request->birthdate)) {
                            return response()->json([
                                'message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula',
                            ], 422);
                        }

                        $cedulaVerifiedAt = now();
                        $sexo = $person['sexo'] ?? null;
                    } else {
                        // La cédula no existe o no devolvió datos válidos
                        return response()->json([
                            'message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula',
                        ], 422);
                    }
                } else {
                    Log::error('Error calling Registro Civil API: ' . $response->body());
                    return response()->json(['message' => 'No se pudo validar la cédula con el Registro Civil en este momento.'], 500);
                }
            } catch (\Exception $e) {
                Log::error('Exception calling Registro Civil API: ' . $e->getMessage());
                return response()->json(['message' => 'Error de conexión al validar la cédula con el Registro Civil.'], 500);
            }
        }

        // 4. Procesamiento si viene con google_token
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

        // 5. Crear el usuario
        $user = User::create([
            'google_id'          => $googleId,
            'name'               => $request->name,
            'lastname'           => $request->lastname,
            'username'           => $request->username,
            'email'              => $email,
            'password'           => Hash::make($request->password), // SIEMPRE usa la que provee el usuario
            'email_verified_at'  => $emailVerifiedAt,
            'phone_number'       => $request->phone_number,
            'cedula'             => $request->cedula,
            'cedula_verified_at' => $cedulaVerifiedAt,
        ]);

        $user->assignRole('student');

        // Crear registro en UserInformation
        $user->userInformation()->create([
            'birthdate' => $request->birthdate,
            'sexo'      => $sexo,
        ]);

        // Autologuear al usuario tras el registro
        return $this->generateLoginResponse($user, $request->has('google_token'));
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
    
    //emailSendCode
    public function emailSendCode(Request $request): JsonResponse {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'El correo electrónico no existe.',
            ], 404);
        }

        
        // Desencadenar flujo de verificación (enviar código)

        return response()->json([
            'message' => 'Código enviado exitosamente.',
        ]);
    }
    
    //phoneSendCode
    public function phoneSendCode(Request $request): JsonResponse {
        $request->validate([
            'phone_number' => 'required',
            'channel'=>'required|in:whatsapp,telegram'
        ]);
        // Desencadenar flujo de verificación (enviar código)
        
    }
    

}
