<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\EcuadorLocationService;
use App\Models\TutorCourse;
use App\Models\Category;
class PortfolioController extends Controller
{
    use AuthorizesRequests;
    public function show(string $username, EcuadorLocationService $locations)
{
    // Verifica que el usuario tiene rol tutor o admin
    $user = User::where('username', $username)
        ->with([
            'educationalUser.career',
            'educationalUser.sede.educationalUnit',
            'tutoredCourses' => fn ($query) =>
                $query->where('enabled', true)
                    ->where('is_owner', true)
                    ->with(['difficulty', 'categories'])
        ])
        ->firstOrFail();

    $this->authorize('viewPortfolio', $user);

    $educationalUser = $user->educationalUser;
    $sede = $educationalUser?->sede;

    // ----------------------------------------
    // Resolver nombres de provincia y cantón
    // ----------------------------------------
    $provinceName = null;
    $cantonName   = null;

    if ($sede && $sede->province_id) {
        // Buscar provincia por ID en el JSON
        $provinces = $locations->getProvinces();
        $province  = collect($provinces)->firstWhere('id', (string) $sede->province_id);

        if ($province) {
            $provinceName = $province['name'];
        }

        // Si hay cantón, buscarlo en el JSON de esa provincia
        if ($sede->canton_id) {
            $cantons = $locations->getCantons((string) $sede->province_id);
            $canton  = collect($cantons)->firstWhere('id', (string) $sede->canton_id);

            if ($canton) {
                $cantonName = $canton['name'];
            }
        }
    }

    return response()->json([
        'message' => 'Portafolio cargado correctamente.',
        'portfolio' => [
            'name'               => $user->name,
            'lastname'           => $user->lastname,
            'username'           => $user->username,
            'email'              => $user->email,
            'profile_picture_url'=> $user->profile_picture_url,
            'joined_at'          => Carbon::parse($user->created_at)
                                ->locale('es')
                                ->translatedFormat('d M Y'),
            'career'             => $educationalUser?->career ?? null,
            'sede'               => $sede ? [
                'id'               => $sede->id,
                // IDs crudos
                'province_id'      => $sede->province_id,
                'canton_id'        => $sede->canton_id,
                // Nombres calculados (igual llaves antiguas para no romper el frontend)
                'province'         => $provinceName,
                'canton'           => $cantonName,
                'educational_unit' => $sede->educationalUnit ?? null,
            ] : null,
            'active_courses_count' => $user->tutoredCourses->count(),
            'role'                 => $user->getRoleNames()[0],
        ]
    ]);
}



    public function index()
    {
        //
    }


}
