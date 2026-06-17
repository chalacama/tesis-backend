<?php

namespace App\Http\Controllers;

use App\Models\UserInformation;
use App\Services\EcuadorLocationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserInformationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Mostrar la información personal del usuario autenticado.
     */
    public function show(EcuadorLocationService $locations): JsonResponse
    {
        $user = Auth::user();

        $this->authorize('viewHidden', $user);

        $user->load('userInformation');

        if (!$user->userInformation) {
            return response()->json([
                'userInformation' => null,
            ]);
        }

        $formatted = $this->formatUserInformation($user->userInformation, $locations);

        return response()->json([
            'userInformation' => $formatted,
        ]);
    }

    /**
     * Crear / actualizar la información personal del usuario.
     */
    public function update(Request $request, EcuadorLocationService $locations): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'cedula' => ['nullable', 'string', 'min:10', 'max:10', \Illuminate\Validation\Rule::unique('users', 'cedula')->ignore($user->id)],
            'name' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'province_id' => ['nullable', 'integer'],
            'canton_id'   => ['nullable', 'integer'],
            'parish_id'   => ['nullable', 'integer'],
            'estado_civil' => [
                'nullable',
                'in:casado/a,unido/a,separado/a,divorciado/a,viudo/a,soltero/a'
            ],
            'discapacidad' => ['nullable', 'in:si,no'],
        ]);

        // Reglas condicionales para ubicación
        $validator->sometimes('canton_id', 'required', function ($input) {
            return !empty($input->province_id);
        });
        $validator->sometimes('parish_id', 'required', function ($input) {
            return !empty($input->canton_id);
        });

        // Reglas condicionales para discapacidad
        $validator->sometimes(
            'discapacidad_permanente',
            'required|in:intelectual (retraso mental),físico-motora (parálisis y amputaciones),visual (ceguera),auditiva (sordera),mental (enfermedades psiquiátricas),otro tipo',
            function ($input) {
                return $input->discapacidad === 'si';
            }
        );

        $validator->sometimes(
            'asistencia_establecimiento_discapacidad',
            'required|in:si,no',
            function ($input) {
                return $input->discapacidad === 'si';
            }
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Validar cédula si viene en el request
        if ($request->has('cedula')) {
            $newCedula = $validated['cedula'] ?? null;
            
            if (is_null($newCedula)) {
                $user->cedula = null;
                $user->cedula_verified_at = null;
                $user->save();
            } elseif ($newCedula !== $user->cedula) {
                // Requerir campos si la cédula cambia
                $cedulaValidator = Validator::make($request->all(), [
                    'name'      => ['required', 'string', 'max:255'],
                    'lastname'  => ['required', 'string', 'max:255'],
                    'birthdate' => ['required', 'date', 'before_or_equal:today'],
                ]);

                if ($cedulaValidator->fails()) {
                    return response()->json([
                        'message' => 'Faltan datos (nombre, apellido, fecha de nacimiento) para validar la nueva cédula',
                        'errors'  => $cedulaValidator->errors(),
                    ], 422);
                }

                // Validación con Registro Civil
                $response = \Illuminate\Support\Facades\Http::asForm()->post('https://si.secap.gob.ec/sisecap/logeo_web/json/busca_persona_registro_civil.php', [
                    'documento' => $newCedula,
                    'tipo'      => '1',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $person = is_array($data) && isset($data[0]) ? $data[0] : $data;

                    if (isset($person['nombres']) && isset($person['apellidos'])) {
                        $apiNombres = preg_replace('/\s+/', ' ', trim(strtoupper($person['nombres'])));
                        $apiApellidos = preg_replace('/\s+/', ' ', trim(strtoupper($person['apellidos'])));
                        $reqName = preg_replace('/\s+/', ' ', trim(strtoupper($validated['name'])));
                        $reqLastname = preg_replace('/\s+/', ' ', trim(strtoupper($validated['lastname'])));
                        
                        $apiBirthdate = null;
                        if (isset($person['fechaNacimiento'])) {
                            try {
                                $apiBirthdate = \Carbon\Carbon::createFromFormat('d/m/Y', $person['fechaNacimiento'])->format('Y-m-d');
                            } catch (\Exception $e) {
                                $apiBirthdate = date('Y-m-d', strtotime(str_replace('/', '-', $person['fechaNacimiento'])));
                            }
                        }

                        if ($apiNombres !== $reqName || $apiApellidos !== $reqLastname || ($apiBirthdate && $apiBirthdate !== $validated['birthdate'])) {
                            return response()->json([
                                'message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula',
                            ], 422);
                        }

                        $user->cedula = $newCedula;
                        $user->name = $validated['name'];
                        $user->lastname = $validated['lastname'];
                        $user->cedula_verified_at = now();
                        $user->save();
                        
                        // Guardaremos el sexo para actualizarlo en UserInformation
                        $validated['sexo'] = $person['sexo'] ?? null;

                    } else {
                        return response()->json(['message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula'], 422);
                    }
                } else {
                    return response()->json(['message' => 'No se pudo validar la cédula con el Registro Civil en este momento.'], 500);
                }
            }
        }

        // Si NO tiene discapacidad, limpiamos siempre estos campos
        if (isset($validated['discapacidad']) && $validated['discapacidad'] === 'no') {
            $validated['discapacidad_permanente'] = null;
            $validated['asistencia_establecimiento_discapacidad'] = null;
        }

        // Quitamos campos que no pertenecen a user_information
        unset($validated['name']);
        unset($validated['lastname']);
        unset($validated['cedula']);

        // Forzamos el user_id
        $validated['user_id'] = $user->id;

        $info = UserInformation::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $formatted = $this->formatUserInformation($info, $locations);

        return response()->json([
            'message' => $info->wasRecentlyCreated
                ? 'Información creada exitosamente.'
                : 'Información actualizada correctamente.',
            'userInformation' => $formatted,
        ]);
    }

    /**
     * Formatea la info del usuario agregando nombres de provincia, cantón y parroquia.
     */
    private function formatUserInformation(UserInformation $info, EcuadorLocationService $locations): array
    {
        $provinceName = null;
        $cantonName   = null;
        $parishName   = null;

        // Provincia
        $provinces = $locations->getProvinces();
        $province = collect($provinces)->firstWhere('id', (string) $info->province_id);
        if ($province) {
            $provinceName = $province['name'];
        }

        // Cantón (depende de la provincia)
        $cantons = $locations->getCantons((string) $info->province_id);
        $canton = collect($cantons)->firstWhere('id', (string) $info->canton_id);
        if ($canton) {
            $cantonName = $canton['name'];
        }

        // Parroquia (depende de provincia + cantón)
        $parishes = $locations->getParishes(
            (string) $info->province_id,
            (string) $info->canton_id
        );
        $parish = collect($parishes)->firstWhere('id', (string) $info->parish_id);
        if ($parish) {
            $parishName = $parish['name'];
        }

        return [
            'id' => $info->id,
            'birthdate' => optional($info->birthdate)->toDateString(),

            'province_id'   => $info->province_id,
            'province_name' => $provinceName,

            'canton_id'   => $info->canton_id,
            'canton_name' => $cantonName,

            'parish_id'   => $info->parish_id,
            'parish_name' => $parishName,

            'sexo' => $info->sexo,
            'estado_civil' => $info->estado_civil,
            'discapacidad' => $info->discapacidad,
            'discapacidad_permanente' => $info->discapacidad_permanente,
            'asistencia_establecimiento_discapacidad' => $info->asistencia_establecimiento_discapacidad,
            'user_id' => $info->user_id,
            'created_at' => $info->created_at,
            'updated_at' => $info->updated_at,
        ];
    }
}
