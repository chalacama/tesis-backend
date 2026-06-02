<?php

namespace App\Http\Controllers;

use App\Mail\SendOTPCode;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class VerificationCodeController extends Controller
{
    /**
     * ====================================================================
     * ENVIAR CÓDIGO OTP
     * ====================================================================
     * Genera un código OTP de 6 dígitos, lo guarda hasheado en la BD,
     * y lo envía al usuario por el canal seleccionado (email o whatsapp).
     *
     * - Para 'email_verification' y 'phone_verification': requiere auth:sanctum
     * - Para 'password_reset': ruta pública, se identifica al usuario por email
     */
    public function sendCode(Request $request): JsonResponse
    {
        // 1. Validación de entrada
        $request->validate([
            'type'    => ['required', Rule::in(['email_verification', 'phone_verification', 'password_reset'])],
            'channel' => ['required', Rule::in(['email', 'whatsapp'])],
            'email'   => 'required_if:type,password_reset|email', // Solo obligatorio para password_reset
        ]);

        $type    = $request->type;
        $channel = $request->channel;

        // 2. Resolver el usuario según el contexto
        if ($type === 'password_reset') {
            // Ruta pública: buscar usuario por email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Seguridad: no revelar si el email existe o no
                return response()->json([
                    'message'            => 'Si el correo existe en nuestro sistema, recibirás un código de verificación.',
                    'code_sent'          => true,
                    'expires_in_seconds' => 900, // 15 min
                ]);
            }
        } else {
            // Ruta protegida: usuario autenticado
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
        }

        // 3. Invalidar códigos anteriores activos del mismo tipo y canal
        VerificationCode::active($user->id, $type, $channel)
            ->update(['used_at' => now()]);

        // 4. Generar código OTP de 6 dígitos
        $plainCode = VerificationCode::generateCode(6);

        // 5. Guardar el código hasheado en la base de datos
        $verificationCode = VerificationCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($plainCode),
            'type'       => $type,
            'channel'    => $channel,
            'expires_at' => now()->addMinutes(15),
        ]);

        // 6. Enviar el código por el canal correspondiente
        if ($channel === 'email') {
            Mail::to($user->email)->send(new SendOTPCode($user, $plainCode, $type));
        } elseif ($channel === 'whatsapp') {
            // ─────────────────────────────────────────────────────────────
            // PLACEHOLDER: Integración con chatbot de WhatsApp (Node.js)
            // Reemplazar este Log por la llamada HTTP a tu servicio de
            // WhatsApp cuando esté listo.
            // ─────────────────────────────────────────────────────────────
            $phoneNumber = $user->phone_number ?? 'N/A';
            Log::info("📱 [WhatsApp OTP] Código enviado", [
                'user_id' => $user->id,
                'phone'   => $phoneNumber,
                'type'    => $type,
                'code'    => $plainCode, // En producción, NUNCA loguear el código real
            ]);
        }

        // 7. Calcular segundos restantes
        $expiresInSeconds = (int) now()->diffInSeconds($verificationCode->expires_at);

        return response()->json([
            'message'            => 'Código de verificación enviado exitosamente.',
            'code_sent'          => true,
            'expires_in_seconds' => $expiresInSeconds,
        ]);
    }


    /**
     * ====================================================================
     * VERIFICAR CÓDIGO OTP
     * ====================================================================
     * Recibe el código plano ingresado por el usuario, busca los códigos
     * activos, compara con Hash::check(), y ejecuta la acción correspondiente.
     *
     * Acciones según 'type':
     * - email_verification: marca email_verified_at
     * - phone_verification: marca phone_verified_at
     * - password_reset: actualiza la contraseña del usuario
     */
    public function verifyCode(Request $request): JsonResponse
    {
        // 1. Validación de entrada
        $rules = [
            'code'    => 'required|string|size:6',
            'type'    => ['required', Rule::in(['email_verification', 'phone_verification', 'password_reset'])],
            'channel' => ['required', Rule::in(['email', 'whatsapp'])],
        ];

        $type = $request->type;

        // Para password_reset: requerir email, nueva contraseña y confirmación
        if ($type === 'password_reset') {
            $rules['email']                 = 'required|email';
            $rules['new_password']          = ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()];
        }

        // Para password_reset público: requerir email
        if ($type === 'password_reset') {
            $rules['email'] = 'required|email';
        }

        $request->validate($rules);

        $channel = $request->channel;

        // 2. Resolver el usuario según el contexto
        if ($type === 'password_reset') {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'No se encontró un usuario con ese correo electrónico.',
                ], 404);
            }
        } else {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
        }

        // 3. Buscar códigos activos (no usados, no expirados)
        $activeCodes = VerificationCode::active($user->id, $type, $channel)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($activeCodes->isEmpty()) {
            return response()->json([
                'message' => 'No hay códigos activos. Solicita uno nuevo.',
            ], 422);
        }

        // 4. Comparar el código ingresado con los hasheados
        $matchedCode = null;

        foreach ($activeCodes as $activeCode) {
            if (Hash::check($request->code, $activeCode->code)) {
                $matchedCode = $activeCode;
                break;
            }
        }

        if (!$matchedCode) {
            return response()->json([
                'message' => 'El código ingresado es incorrecto.',
            ], 422);
        }

        // 5. Marcar el código como usado
        $matchedCode->markAsUsed();

        // 6. Ejecutar la acción según el tipo
        switch ($type) {
            case 'email_verification':
                $user->email_verified_at = now();
                $user->save();
                $responseMessage = 'Correo electrónico verificado exitosamente.';
                break;

            case 'phone_verification':
                $user->phone_verified_at = now();
                $user->save();
                $responseMessage = 'Número de teléfono verificado exitosamente.';
                break;

            case 'password_reset':
                $user->password = Hash::make($request->new_password);
                $user->save();
                // Revocar todos los tokens del usuario por seguridad
                $user->tokens()->delete();
                $responseMessage = 'Contraseña actualizada exitosamente. Por favor, inicia sesión nuevamente.';
                break;

            default:
                $responseMessage = 'Verificación completada.';
        }

        return response()->json([
            'message'  => $responseMessage,
            'verified' => true,
        ]);
    }


    /**
     * ====================================================================
     * CONSULTAR ESTADO DEL CÓDIGO
     * ====================================================================
     * Verifica si existe un código activo para el usuario y devuelve
     * los segundos restantes. Útil para cuando el usuario recarga la pestaña
     * o cierra accidentalmente el navegador.
     */
    public function checkStatus(Request $request): JsonResponse
    {
        // 1. Validación de entrada
        $request->validate([
            'type'  => ['required', Rule::in(['email_verification', 'phone_verification', 'password_reset'])],
            'email' => 'required_if:type,password_reset|email',
        ]);

        $type = $request->type;

        // 2. Resolver el usuario según el contexto
        if ($type === 'password_reset') {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Seguridad: no revelar si el email existe
                return response()->json([
                    'code_sent'          => false,
                    'expires_in_seconds' => 0,
                ]);
            }
        } else {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
        }

        // 3. Buscar el código activo más reciente
        $activeCode = VerificationCode::active($user->id, $type)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$activeCode) {
            return response()->json([
                'code_sent'          => false,
                'expires_in_seconds' => 0,
            ]);
        }

        // 4. Calcular segundos restantes
        $secondsRemaining = (int) now()->diffInSeconds($activeCode->expires_at, false);

        // Si la diferencia es negativa, el código ya expiró
        if ($secondsRemaining <= 0) {
            return response()->json([
                'code_sent'          => false,
                'expires_in_seconds' => 0,
            ]);
        }

        return response()->json([
            'code_sent'          => true,
            'expires_in_seconds' => $secondsRemaining,
        ]);
    }
}
