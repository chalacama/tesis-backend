<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class IdentifierController extends Controller
{
    
    public function validateUsername(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._-]+$/',
            ],
        ], [
            'username.regex' => 'El usuario solo puede contener letras minúsculas, números, puntos (.) y guiones (_-). Sin espacios ni mayúsculas.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Formato inválido.',
                'errors' => $validator->errors(),
                'is_available' => false,
            ], 422);
        }

        $username = $request->input('username');

        // Comprobar existencia en la base de datos
        if (User::where('username', $username)->exists()) {
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

    public function validateEmail(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Formato de correo inválido.',
                'errors' => $validator->errors(),
                'is_available' => false,
            ], 422);
        }

        $email = $request->input('email');

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'message' => 'Este correo ya está registrado.',
                'is_available' => false,
            ]);
        }

        return response()->json([
            'message' => 'Correo válido y disponible.',
            'is_available' => true,
        ]);
    }

    public function validatePhone(Request $request)
    {
        $validator = \Validator::make($request->all(), [
           'phone_number' => ['required', 'regex:/^\+593[0-9]{9}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Formato de teléfono inválido.',
                'errors' => $validator->errors(),
                'is_available' => false,
            ], 422);
        }

        $phone = $request->input('phone_number');

        if (User::where('phone_number', $phone)->exists()) {
            return response()->json([
                'message' => 'Este teléfono ya está registrado.',
                'is_available' => false,
            ]);
        }

        return response()->json([
            'message' => 'Teléfono válido y disponible.',
            'is_available' => true,
        ]);
    }

    public function validateCedula(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'cedula' => 'required|string|max:10',
            'name' => 'required|string',
            'lastname' => 'required|string',
            'birthdate' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
                'is_available' => false,
            ], 422);
        }

        $cedula = $request->input('cedula');

        if (User::where('cedula', $cedula)->exists()) {
            return response()->json([
                'message' => 'Esta cédula ya está registrada.',
                'is_available' => false,
            ]);
        }

        // Validación con Registro Civil
        try {
            $response = \Illuminate\Support\Facades\Http::asForm()->post('https://si.secap.gob.ec/sisecap/logeo_web/json/busca_persona_registro_civil.php', [
                'documento' => $cedula,
                'tipo'      => '1',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $person = is_array($data) && isset($data[0]) ? $data[0] : $data;

                if (isset($person['nombres']) && isset($person['apellidos'])) {
                    $apiNombres = preg_replace('/\s+/', ' ', trim(strtoupper($person['nombres'])));
                    $apiApellidos = preg_replace('/\s+/', ' ', trim(strtoupper($person['apellidos'])));
                    $reqName = preg_replace('/\s+/', ' ', trim(strtoupper($request->name)));
                    $reqLastname = preg_replace('/\s+/', ' ', trim(strtoupper($request->lastname)));
                    
                    $apiBirthdate = null;
                    if (isset($person['fechaNacimiento'])) {
                        try {
                            $apiBirthdate = \Carbon\Carbon::createFromFormat('d/m/Y', $person['fechaNacimiento'])->format('Y-m-d');
                        } catch (\Exception $e) {
                            $apiBirthdate = date('Y-m-d', strtotime(str_replace('/', '-', $person['fechaNacimiento'])));
                        }
                    }

                    if ($apiNombres !== $reqName || $apiApellidos !== $reqLastname || ($apiBirthdate && $apiBirthdate !== $request->birthdate)) {
                        return response()->json([
                            'message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula',
                            'is_available' => false,
                        ], 422);
                    }

                    return response()->json([
                        'message' => 'Cédula válida y disponible.',
                        'is_available' => true,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula',
                        'is_available' => false,
                    ], 422);
                }
            } else {
                return response()->json([
                    'message' => 'No se pudo validar la cédula con el Registro Civil en este momento.',
                    'is_available' => false,
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error de conexión al validar la cédula con el Registro Civil.',
                'is_available' => false,
            ], 500);
        }
    }
}