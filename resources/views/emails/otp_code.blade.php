<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Código de Verificación - DigiMentor</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Instrument Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #1b1b18;
            background-color: #f4f4f2;
            padding: 20px;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 32px 24px;
            text-align: center;
        }

        .email-header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .email-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            margin-top: 6px;
        }

        .email-body {
            padding: 32px 24px;
        }

        .greeting {
            font-size: 16px;
            color: #374151;
            margin-bottom: 16px;
        }

        .greeting strong {
            color: #1b1b18;
        }

        .description {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
            line-height: 1.7;
        }

        .code-container {
            text-align: center;
            padding: 24px;
            background-color: #f9fafb;
            border: 2px dashed #e5e7eb;
            border-radius: 10px;
            margin-bottom: 24px;
        }

        .code-label {
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .code-value {
            font-size: 42px;
            font-weight: 700;
            letter-spacing: 12px;
            color: #4f46e5;
            font-family: 'Courier New', monospace;
            padding: 8px 0;
        }

        .expiry-notice {
            font-size: 12px;
            color: #ef4444;
            margin-top: 8px;
            font-weight: 500;
        }

        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 14px 16px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 24px;
        }

        .info-box p {
            font-size: 13px;
            color: #1e40af;
            line-height: 1.6;
        }

        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 14px 16px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 16px;
        }

        .warning-box p {
            font-size: 13px;
            color: #92400e;
            line-height: 1.6;
        }

        .email-footer {
            padding: 20px 24px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }

        .email-footer p {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }

        .email-footer .brand {
            font-weight: 600;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        {{-- ═══════════ HEADER ═══════════ --}}
        <div class="email-header">
            <h1>DigiMentor ESPAM</h1>
            <p>
                @if($type === 'email_verification')
                    Verificación de correo electrónico
                @elseif($type === 'phone_verification')
                    Verificación de número de teléfono
                @elseif($type === 'password_reset')
                    Restablecimiento de contraseña
                @else
                    Código de verificación
                @endif
            </p>
        </div>

        {{-- ═══════════ BODY ═══════════ --}}
        <div class="email-body">
            <p class="greeting">
                Hola, <strong>{{ $user->name }} {{ $user->lastname }}</strong> 👋
            </p>

            <p class="description">
                @if($type === 'email_verification')
                    Recibimos una solicitud para verificar tu dirección de correo electrónico.
                    Ingresa el siguiente código en la plataforma para completar tu verificación:
                @elseif($type === 'phone_verification')
                    Recibimos una solicitud para verificar tu número de teléfono.
                    Ingresa el siguiente código en la plataforma para completar tu verificación:
                @elseif($type === 'password_reset')
                    Recibimos una solicitud para restablecer tu contraseña.
                    Ingresa el siguiente código en la plataforma para continuar con el proceso:
                @endif
            </p>

            {{-- ═══════════ CÓDIGO OTP ═══════════ --}}
            <div class="code-container">
                <p class="code-label">Tu código de verificación</p>
                <p class="code-value">{{ $code }}</p>
                <p class="expiry-notice">⏰ Este código expira en 15 minutos</p>
            </div>

            {{-- ═══════════ INFORMACIÓN ═══════════ --}}
            <div class="info-box">
                <p>
                    <strong>💡 Consejo:</strong> Copia y pega el código directamente para evitar errores de escritura.
                </p>
            </div>

            {{-- ═══════════ ADVERTENCIA ═══════════ --}}
            <div class="warning-box">
                <p>
                    <strong>⚠️ Importante:</strong> Si no solicitaste este código, ignora este correo.
                    Tu cuenta está segura y no se ha realizado ningún cambio.
                </p>
            </div>
        </div>

        {{-- ═══════════ FOOTER ═══════════ --}}
        <div class="email-footer">
            <p>Este correo fue enviado automáticamente por <span class="brand">DigiMentor ESPAM</span>.</p>
            <p>Por favor, no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
