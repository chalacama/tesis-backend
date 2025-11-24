<?php

namespace App\Http\Controllers;

use App\Models\UserInformation;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserInformationController extends Controller
{
    use AuthorizesRequests;

    public function show(): JsonResponse
    {
        $user = Auth::user();

        $this->authorize('viewHidden', $user);

        $user->load('userInformation');

        return response()->json([
            'userInformation' => $user->userInformation,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'phone_number' => ['required', 'regex:/^\+593[0-9]{9}$/'],
            'province' => ['required', 'string', 'max:100'],
            'canton' => ['required', 'string', 'max:100'],
            'parish' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'in:masculino,femenino'],
            'estado_civil' => ['required', 'in:casado/a,unido/a,separado/a,divorciado/a,viudo/a,soltero/a'],
            'discapacidad' => ['required', 'in:si,no'],
        ]);

        // Reglas condicionales para discapacidad
        $validator->sometimes('discapacidad_permanente', 'required|in:intelectual (retraso mental),físico-motora (parálisis y amputaciones),visual (ceguera),auditiva (sordera),mental (enfermedades psiquiátricas),otro tipo', function ($input) {
            return $input->discapacidad === 'si';
        });

        $validator->sometimes('asistencia_establecimiento_discapacidad', 'required|in:si,no', function ($input) {
            return $input->discapacidad === 'si';
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $info = UserInformation::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => $info->wasRecentlyCreated
                ? 'Información creada exitosamente.'
                : 'Información actualizada correctamente.',
            'userInformation' => $info,
        ]);
    }
}
