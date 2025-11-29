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

    public function update(Request $request)
    {
        $user = $request->user();

        $this->authorize('update', $user);

        // Validación para username
        $validatedData = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
        ], [
            'username.regex' => 'El usuario solo puede contener letras minúsculas, números, puntos (.) y guiones bajos (_). Sin espacios ni mayúsculas.',
            'username.unique' => 'Este nombre de usuario ya está ocupado.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
        ]);

        $newUsername = $validatedData['username'];

        // Si es el mismo username, no tiene sentido guardar ni tocar username_at
        if ($newUsername === $user->username) {
            return response()->json([
                'message'  => 'No se realizaron cambios en el nombre de usuario.',
                'username' => $user->username,
                'username_at' => optional($user->username_at)->toDateTimeString(),
            ]);
        }

        // Regla de tiempo:
        // - Si username_at es null -> primer cambio permitido siempre
        // - Si NO es null -> debe haber pasado al menos 3 meses
        if ($user->username_at !== null) {
            $limiteTresMeses = $user->username_at->copy()->addMonths(3);

            if (now()->lt($limiteTresMeses)) {
                return response()->json([
                    'message' => 'Solo puedes cambiar tu nombre de usuario cada 3 meses.',
                    'next_allowed_change_at' => $limiteTresMeses->toDateTimeString(),
                ], 422);
            }
        }

        // Aquí ya está permitido el cambio
        $user->username    = $newUsername;
        $user->username_at = now(); // registramos la fecha del ÚLTIMO cambio
        $user->save();

        return response()->json([
            'message'       => 'User actualizado con éxito',
            'username'      => $user->username,
            'username_at'   => $user->username_at->toDateTimeString(),
        ]);
    }

    public function validateUsername(Request $request)
    {
        $user = $request->user();

        $this->authorize('update', $user);

        // Validar formato (sin verificar unicidad aún)
        $validator = \Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._-]+$/',
            ],
        ], [
            'username.regex' => 'El usuario solo puede contener letras minúsculas, números, puntos (.) y guiones bajos (_). Sin espacios ni mayúsculas.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validación fallida.',
                'errors' => $validator->errors(),
                'is_available' => false,
            ], 422);
        }

        $username = $request->input('username');

        // Si es el mismo que el actual, lo consideramos disponible
        if ($username === $user->username) {
            return response()->json([
                'message' => 'El nombre de usuario es igual al actual.',
                'is_available' => false,
            ]);
        }

        // Comprobar existencia en la base de datos
        $exists = User::where('username', $username)->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este nombre de usuario ya está ocupado.',
                'is_available' => false,
            ]);
        }

        return response()->json([
            'message' => 'El nombre de usuario es válido y está disponible.',
            'is_available' => true,
        ]);
    }

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
