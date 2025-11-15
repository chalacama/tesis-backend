<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Sede;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\EducationalUnit;
class SedeController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Base query con relaciones
        $query = Sede::with([
            'educationalUnit.educationalLevels', // niveles de educación
            'careers',                           // carreras por sede
        ]);

        // Si es admin, ve todo sin filtros
        if (!$user->hasRole('admin')) {
            $email = $user->email;
            $domain = null;

            if ($email && str_contains($email, '@')) {
                $domain = strtolower(substr(strrchr($email, '@'), 1)); // parte después de @
            }

            // Caso: usuario con gmail (sin dominio institucional)
            if ($domain === 'gmail.com') {
                // Solo sedes cuya unidad educativa NO tiene dominio
                $query->whereHas('educationalUnit', function ($q) {
                    $q->whereNull('organization_domain');
                });
            } elseif ($domain) {
                // Verificamos si el dominio existe en alguna unidad educativa
                $existsDomain = EducationalUnit::where('organization_domain', $domain)->exists();

                if ($existsDomain) {
                    // Mostrar solo sedes de unidades con ese dominio
                    $query->whereHas('educationalUnit', function ($q) use ($domain) {
                        $q->where('organization_domain', $domain);
                    });
                } else {
                    // Dominio NO registrado en ninguna unidad educativa → no mostrar sedes
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Por seguridad, si no se pudo obtener dominio, no mostramos nada
                $query->whereRaw('1 = 0');
            }
        }

        $sedes = $query->get();

        return response()->json([
            'sedes' => $sedes,
        ]);
    }
}
