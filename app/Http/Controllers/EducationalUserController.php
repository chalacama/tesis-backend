<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EducationalUser;
use Illuminate\Http\JsonResponse;
class EducationalUserController extends Controller
{
    use AuthorizesRequests;
    public function show()
    {
        $user = Auth::user();

        // Autorización con policy existente
        $this->authorize('viewHidden', $user);

        // Cargar la relación con datos educativos
        $user->load(
        'educationalUser.educationalLevel', 
        'educationalUser.sede',
        'educationalUser.sede.educationalUnit',
        'educationalUser.career' 
        );

        return response()->json([
            'educationalUser' => $user->educationalUser
        ]);
    }
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        $validated = $request->validate([
            'sede_id'              => ['required', 'exists:sedes,id'],
            'career_id'           => ['nullable', 'exists:careers,id'],
            'educational_level_id'=> ['nullable', 'exists:educational_levels,id'],
            'level'               => ['nullable', 'integer', 'min:1'],
        ]);

        $info = EducationalUser::updateOrCreate(
            ['user_id' => $user->id],
            $validated + ['user_id' => $user->id] // garantiza que no se sobrescriba el user_id
        );

        return response()->json([
            'message' => $info->wasRecentlyCreated ? 'Información educativa creada exitosamente.' : 'Información educativa actualizada correctamente.',
            'educationalUser' => $info->load('educationalLevel', 'sede', 'career')
        ]);
    }
}
