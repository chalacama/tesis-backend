<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitación para colaborar en un curso</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles -->
    <style>
        body {
            font-family: 'Instrument Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #1b1b18;
            background-color: #FDFDFC;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #0a0a0a;
                color: #EDEDEC;
            }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen flex-col">
    <div class="container">
        <h1>¡Has sido invitado a colaborar en un curso!</h1>
        <p>Hola,</p>
        <p>Has sido invitado a colaborar en el curso <strong>{{ $invitation->course->title }}</strong>.</p>
        <p>Para aceptar la invitación, haz clic en el siguiente enlace:</p>
        <p><a href="{{ url('/invitation/accept/' . $invitation->token) }}" class="button">Aceptar Invitación</a></p>
        <p>Detalles de la invitación:</p>
        <ul>
            <li>Correo invitado: {{ $invitation->email }}</li>
            <li>Curso: {{ $invitation->course->title }}</li>
            <li>Invitador ID: {{ $invitation->inviter_id }}</li>
        </ul>
        <p>Si no deseas aceptar, simplemente ignora este correo.</p>
        <p>Gracias,<br>Equipo de la plataforma</p>
    </div>
</body>
</html>