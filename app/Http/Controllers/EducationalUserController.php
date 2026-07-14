<?php 

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

use App\Models\EducationalUser;
use App\Models\Sede;
use App\Models\EducationalUnit;
use App\Services\EcuadorLocationService;

class EducationalUserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Mostrar la información educativa del usuario autenticado,
     * incluyendo nombres de provincia y cantón de la sede.
     */
    public function show(EcuadorLocationService $locations): JsonResponse
    {
        $user = Auth::user();

        // Autorización con policy existente
        $this->authorize('viewHidden', $user);

        // Cargar la relación con datos educativos y sede
        $user->load(
            'educationalUser.educationalLevel', 
            'educationalUser.sede',
            'educationalUser.sede.educationalUnit',
            'educationalUser.career' 
        );

        if (!$user->educationalUser) {
            return response()->json([
                'educationalUser' => null,
            ]);
        }

        $formatted = $this->formatEducationalUser($user->educationalUser, $locations);

        return response()->json([
            'educationalUser' => $formatted,
        ]);
    }

    /**
     * Crear / actualizar información educativa del usuario.
     * Valida:
     *  - Entrada (ids existen)
     *  - Que la sede seleccionada sea válida según dominio del correo del usuario.
     */
    public function update(Request $request, EcuadorLocationService $locations): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        // 1) Validación de entrada (ids existen)
        $validated = $request->validate([
            'sede_id'               => ['required', 'exists:sedes,id'],
            'career_id'             => ['nullable', 'exists:careers,id'],
            'educational_level_id'  => ['required', 'exists:educational_levels,id'],
            'level'                 => ['nullable', 'integer', 'min:1'],
            'finished'              => ['nullable', 'boolean'],
        ]);

        // 2) Validar que la sede sea "permitida" para este usuario
        /** @var \App\Models\Sede $sede */
        $sede = Sede::with('educationalUnit')->findOrFail($validated['sede_id']);

        // Admin puede seleccionar cualquier sede
        if (!$user->hasRole('admin')) {
            $email = $user->email;
            $domain = null;

            if ($email && str_contains($email, '@')) {
                $domain = strtolower(substr(strrchr($email, '@'), 1));
            }

            $hasInstitutionalMatch = false;

            if ($domain) {
                $hasInstitutionalMatch = EducationalUnit::where('organization_domain', $domain)->exists();
            }

            if ($domain === 'gmail.com' || !$domain || !$hasInstitutionalMatch) {
                // Usuarios sin correo, con correos personales o con un dominio institucional no registrado
                // solo pueden seleccionar sedes de unidades educativas sin dominio.
                $sedeDomain = optional($sede->educationalUnit)->organization_domain;

                if ($sedeDomain) {
                    return response()->json([
                        'message' => 'No puedes seleccionar esta sede con tu correo actual.',
                    ], 403);
                }
            } elseif ($domain) {
                // Dominio institucional registrado: la sede debe coincidir con ese dominio
                $sedeDomain = optional($sede->educationalUnit)->organization_domain;

                if (!$sedeDomain || strtolower($sedeDomain) !== $domain) {
                    return response()->json([
                        'message' => 'No puedes seleccionar esta sede con tu correo institucional.',
                    ], 403);
                }
            }
        }

        // 3) Guardar / actualizar registro
        $info = EducationalUser::updateOrCreate(
            ['user_id' => $user->id],
            $validated + ['user_id' => $user->id] // garantiza que no se sobrescriba el user_id
        );

        // Recargar relaciones para formatear respuesta
        $info->load('educationalLevel', 'sede.educationalUnit', 'career');

        $formatted = $this->formatEducationalUser($info, $locations);

        return response()->json([
            'message' => $info->wasRecentlyCreated
                ? 'Información educativa creada exitosamente.'
                : 'Información educativa actualizada correctamente.',
            'educationalUser' => $formatted,
        ]);
    }

    /**
     * Formatea la información educativa agregando:
     *  - province_id, province_name
     *  - canton_id, canton_name
     * dentro de la sede.
     */
    private function formatEducationalUser(EducationalUser $eduUser, EcuadorLocationService $locations): array
    {
        $sede = $eduUser->sede;

        $provinceId   = null;
        $provinceName = null;
        $cantonId     = null;
        $cantonName   = null;

        if ($sede) {
            $provinceId = $sede->province_id;
            $cantonId   = $sede->canton_id;

            if ($provinceId) {
                // Nombre de provincia
                $provinces = $locations->getProvinces();
                $province = collect($provinces)->firstWhere('id', (string) $provinceId);
                if ($province) {
                    $provinceName = $province['name'];
                }

                // Nombre de cantón
                if ($cantonId) {
                    $cantons = $locations->getCantons((string) $provinceId);
                    $canton  = collect($cantons)->firstWhere('id', (string) $cantonId);
                    if ($canton) {
                        $cantonName = $canton['name'];
                    }
                }
            }
        }

        return [
            'id'                   => $eduUser->id,
            'user_id'              => $eduUser->user_id,
            'sede_id'              => $eduUser->sede_id,
            'career_id'            => $eduUser->career_id,
            'educational_level_id' => $eduUser->educational_level_id,
            'level'                => $eduUser->level,
            'finished'             => $eduUser->finished,
            'created_at'           => $eduUser->created_at,
            'updated_at'           => $eduUser->updated_at,

            // Sede con nombres de provincia/cantón
            'sede' => $sede ? [
                'id'              => $sede->id,
                'contry'          => $sede->contry,
                'province_id'     => $provinceId,
                'province_name'   => $provinceName,
                'canton_id'       => $cantonId,
                'canton_name'     => $cantonName,
                'educational_unit'=> $sede->educationalUnit,
            ] : null,

            // Relaciones directas
            'educational_level' => $eduUser->educationalLevel,
            'career'            => $eduUser->career,
        ];
    }
}
