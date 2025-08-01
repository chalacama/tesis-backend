<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\UserInformation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
class UserInformationController extends Controller
{
    use AuthorizesRequests;
    public function show(): JsonResponse
    {
        
        $user = Auth::user();

        // Aplicar la policy 'viewHidden'
        $this->authorize('viewHidden', $user);

        // Cargar la relación con la información
        $user->load('userInformation');
        return response()->json([
            'userInformation' => $user->userInformation,
        ]);
    }
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Autorizar con Policy
        $this->authorize('update', $user);

        // Validación de entrada
        $validated = $request->validate([
            'birthdate'     => ['nullable', 'date'],
            'phone_number'  => ['nullable', 'string', 'max:20'],
            'province'      => ['nullable', 'string', 'max:100'],
            'canton'        => ['nullable', 'string', 'max:100'],
            'parish'        => ['nullable', 'string', 'max:100'],
        ]);

        // Actualizar o crear la información
        $info = UserInformation::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => $info->wasRecentlyCreated ? 'Información creada exitosamente.' : 'Información actualizada correctamente.',
            'userInformation' => $info
        ]);
    }

}
