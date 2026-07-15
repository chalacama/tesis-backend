<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
            ->select(['id', 'name', 'lastname', 'username', 'profile_picture_url', 'email', 'phone_number', 'cedula', 'email_verified_at', 'phone_verified_at', 'cedula_verified_at', 'created_at'])
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
                'phone_number'        => $user->phone_number,
                'cedula'              => $user->cedula,
                'email_verified_at'   => $user->email_verified_at,
                'phone_verified_at'   => $user->phone_verified_at,
                'cedula_verified_at'  => $user->cedula_verified_at,
                'profile_picture_url' => $user->profile_picture_url,
                'rol'                 => $role?->name, // nombre del rol (admin, tutor, student, etc.)
                'role_id'             => $role?->id,
            ];
        });

        // Respuesta JSON con meta de paginación incluida
        return response()->json($paginator);
    }
    /**
     * Actualizar usuario: rol, email, teléfono, y cédula.
     * PUT /api/user/{user}/update
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role_id'            => ['nullable', 'integer', 'exists:roles,id'],
            'email'              => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number'       => ['nullable', 'string', 'max:13', 'regex:/^\+593[0-9]{9}$/', Rule::unique('users')->ignore($user->id)],
            'cedula'             => ['nullable', 'string', 'max:10', Rule::unique('users')->ignore($user->id)],
            'username'           => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'           => ['nullable', 'string', 'min:8'],
            'admin_password'     => ['nullable', 'string'],
            'logout_all_sessions' => ['nullable', 'boolean'],
            'name'               => ['nullable', 'string', 'max:255'],
            'lastname'           => ['nullable', 'string', 'max:255'],
            'birthdate'          => ['nullable', 'date_format:Y-m-d'],
        ]);

        $authUser = $request->user();

        if ($request->has('role_id') && $request->filled('role_id')) {
            $role = Role::findOrFail($request->role_id);
            $user->syncRoles([$role->name]);
        }

        if ($request->has('email')) {
            $user->email = $request->email;
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
                $user->google_id = null;
            }
        }

        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
            if ($user->isDirty('phone_number')) {
                $user->phone_verified_at = null;
            }
        }

        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('lastname')) {
            $user->lastname = $request->lastname;
        }

        if ($request->has('birthdate')) {
            $user->birthdate = $request->birthdate;
        }

        if ($request->filled('password')) {
            if (! $authUser || ! $authUser->hasRole('admin')) {
                return response()->json([
                    'message' => 'Solo un administrador puede cambiar la contraseña de este usuario.',
                ], 403);
            }

            if (! $request->filled('admin_password') || ! Hash::check($request->admin_password, $authUser->password)) {
                return response()->json([
                    'message' => 'Debes confirmar con tu contraseña para cambiar la contraseña de este usuario.',
                ], 403);
            }

            $user->password = Hash::make($request->password);
        }

        if ($request->has('logout_all_sessions') && $request->boolean('logout_all_sessions')) {
            $user->tokens()->delete();
        }

        if ($request->has('cedula')) {
            $newCedula = $request->cedula;

            if (is_null($newCedula)) {
                $user->cedula = null;
                $user->cedula_verified_at = null;
            } elseif ($newCedula !== $user->cedula) {
                $response = Http::asForm()->post('https://si.secap.gob.ec/sisecap/logeo_web/json/busca_persona_registro_civil.php', [
                    'documento' => $newCedula,
                    'tipo'      => '1',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $person = is_array($data) && isset($data[0]) ? $data[0] : $data;

                    if (isset($person['nombres']) && isset($person['apellidos'])) {
                        $apiNombres = preg_replace('/\s+/', ' ', trim(strtoupper($person['nombres'])));
                        $apiApellidos = preg_replace('/\s+/', ' ', trim(strtoupper($person['apellidos'])));
                        $reqName = preg_replace('/\s+/', ' ', trim(strtoupper($request->input('name', $user->name))));
                        $reqLastname = preg_replace('/\s+/', ' ', trim(strtoupper($request->input('lastname', $user->lastname))));

                        $apiBirthdate = null;
                        if (isset($person['fechaNacimiento'])) {
                            try {
                                $apiBirthdate = Carbon::createFromFormat('d/m/Y', $person['fechaNacimiento'])->format('Y-m-d');
                            } catch (\Exception $e) {
                                $apiBirthdate = date('Y-m-d', strtotime(str_replace('/', '-', $person['fechaNacimiento'])));
                            }
                        }

                        $expectedBirthdate = $request->input('birthdate', $user->birthdate);
                        if ($apiNombres !== $reqName || $apiApellidos !== $reqLastname || ($apiBirthdate && $apiBirthdate !== $expectedBirthdate)) {
                            return response()->json([
                                'message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula',
                            ], 422);
                        }

                        $user->cedula = $newCedula;
                        $user->cedula_verified_at = now();

                        $userInfo = $user->userInformation()->firstOrCreate(['user_id' => $user->id]);
                        $userInfo->sexo = $person['sexo'] ?? $userInfo->sexo;
                        $userInfo->birthdate = $expectedBirthdate;
                        $userInfo->save();
                    } else {
                        return response()->json(['message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula'], 422);
                    }
                } else {
                    return response()->json(['message' => 'No se pudo validar la cédula con el Registro Civil en este momento.'], 500);
                }
            }
        }

        $user->save();
        $user->load('roles:id,name');
        $currentRole = $user->roles->first();

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => [
                'id'                  => $user->id,
                'name'                => $user->name,
                'lastname'            => $user->lastname,
                'username'            => $user->username,
                'email'               => $user->email,
                'phone_number'        => $user->phone_number,
                'cedula'              => $user->cedula,
                'profile_picture_url' => $user->profile_picture_url,
                'rol'                 => $currentRole?->name,
                'role_id'             => $currentRole?->id,
            ],
        ]);
    }

}
