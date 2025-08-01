<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Sede;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class SedeController extends Controller
{
    use AuthorizesRequests;
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Sede::class);

        $sedes = Sede::with('educationalUnit')->get();

        return response()->json([
            'sedes' => $sedes
        ]);
    }
}
