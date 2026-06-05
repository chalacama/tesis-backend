<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Listar usuarios con filtros, orden y paginación.
     */
    public function index(Request $request): JsonResponse
    {
        // Query base
        $query = User::query()
            ->select(['id', 'name', 'lastname', 'username', 'profile_picture_url', 'email', 'created_at'])
            ->with(['roles:id,name']); // usa HasRoles del modelo User

        /**
         * 1) Filtro de búsqueda por nombre, apellido o email
         *    Parámetro: ?search=texto
         */
        if ($search = $request->input('search')) {
            $search = trim($search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('lastname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        /**
         * 2) Filtro por rol (id del rol de Spatie)
         *    Parámetro: ?role_id=1
         */
        if ($roleId = $request->input('role_id')) {
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('id', $roleId);
            });
        }

        /**
         * 3) Ordenamiento
         *    sort = recent      -> registrados más recientes
         *    sort = oldest      -> registrados más antiguos
         *    sort = alpha_asc   -> A-Z (por defecto)
         *    sort = alpha_desc  -> Z-A
         */
        $sort = $request->input('sort', 'alpha_asc');

        switch ($sort) {
            case 'recent':
                $query->orderByDesc('created_at');
                break;

            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'alpha_desc':
                $query->orderBy('name', 'desc')
                    ->orderBy('lastname', 'desc');
                break;

            case 'alpha_asc':
            default:
                $query->orderBy('name', 'asc')
                    ->orderBy('lastname', 'asc');
                break;
        }

        /**
         * 4) Paginación
         *    Parámetro: ?per_page=20
         */
        $perPage = (int) $request->input('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $paginator = $query->paginate($perPage);

        // Transformar la colección para devolver solo los datos necesarios
        $paginator->getCollection()->transform(function (User $user) {
            $role = $user->roles->first(); // asumes 1 rol principal

            return [
                'id'                  => $user->id,
                'name'                => $user->name,
                'lastname'            => $user->lastname,
                'username'            => $user->username,
                'email'               => $user->email,
                'profile_picture_url' => $user->profile_picture_url,
                'rol'                 => $role?->name, // nombre del rol (admin, tutor, student, etc.)
                'role_id'             => $role?->id,
            ];
        });

        // Respuesta JSON con meta de paginación incluida
        return response()->json($paginator);
    }
    /**
     * Cambiar el rol principal de un usuario.
     * PUT /api/user/{user}/change-role
     * Body JSON: { "role_id": 1 }
     */
    public function changeRole(Request $request, User $user): JsonResponse
    {
        // 1) Validar role_id
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        // 2) Buscar el rol
        $role = Role::findOrFail($validated['role_id']);

        // 3) Asignar solo este rol al usuario (remueve los anteriores)
        $user->syncRoles([$role->name]);

        // 4) Recargar relación roles para responder con datos actualizados
        $user->load('roles:id,name');

        $currentRole = $user->roles->first();

        return response()->json([
            'message' => 'Rol actualizado correctamente.',
            'data' => [
                'id'                  => $user->id,
                'name'                => $user->name,
                'lastname'            => $user->lastname,
                'username'            => $user->username,
                'email'               => $user->email,
                'profile_picture_url' => $user->profile_picture_url,
                'rol'                 => $currentRole?->name,
                'role_id'             => $currentRole?->id,
            ],
        ]);
    }

}
