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
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            //'phone_number' => ['required', 'regex:/^\+593[0-9]{9}$/'],

            // IDs de ubicación (ya no textos)
            'province_id' => ['required', 'integer'],
            'canton_id'   => ['required', 'integer'],
            'parish_id'   => ['required', 'integer'],

            'sexo' => ['required', 'in:MUJER,HOMBRE'],
            'estado_civil' => [
                'required',
                'in:casado/a,unido/a,separado/a,divorciado/a,viudo/a,soltero/a'
            ],
            'discapacidad' => ['required', 'in:si,no'],
        ]);

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

        // Si NO tiene discapacidad, limpiamos siempre estos campos
        if ($validated['discapacidad'] === 'no') {
            $validated['discapacidad_permanente'] = null;
            $validated['asistencia_establecimiento_discapacidad'] = null;
        }

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
            //'phone_number' => $info->phone_number,

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
