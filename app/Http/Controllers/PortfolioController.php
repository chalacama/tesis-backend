<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class PortfolioController extends Controller
{
    use AuthorizesRequests;
    public function show(string $username)
    {
        // Verifica que el usuario tiene rol tutor o admin
        $user = User::where('username', $username)
        ->with([
            'educationalUser.career',
            'educationalUser.sede.educationalUnit',
            'tutoredCourses' => fn ($query) =>
                $query->where('enabled', true)->with(['difficulty', 'categories'])
        ])
        ->firstOrFail();

    $this->authorize('viewPortfolio', $user);

        $educationalUser = $user->educationalUser;

        return response()->json([
            'message' => 'Portafolio cargado correctamente.',
            'portfolio' => [
                'name' => $user->name,
                'lastname' => $user->lastname,
                'username' => $user->username,
                'email' => $user->email,
                'joined_at' => Carbon::parse($user->created_at)->locale('es')->translatedFormat('d M Y'),
                'career' => $educationalUser?->career ?? null,
                'sede' => $educationalUser?->sede ? [
                    'id' => $educationalUser->sede->id,
                    'province' => $educationalUser->sede->province,
                    'canton' => $educationalUser->sede->canton,
                    'educational_unit' => $educationalUser->sede->educationalUnit ?? null
                ] : null,
                'active_courses_count' => $user->tutoredCourses->count(),
            ]
        ]);
    }
}
