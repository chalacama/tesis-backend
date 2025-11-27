<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Listar todos los roles.
     * GET /api/role/index
     */
    public function index(): JsonResponse
    {
        // Traer todos los roles ordenados alfabéticamente
        $roles = Role::select('id', 'name', 'guard_name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'data' => $roles,
        ]);
    }
}
