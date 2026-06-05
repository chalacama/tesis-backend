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
            'role_id'      => ['nullable', 'integer', 'exists:roles,id'],
            'email'        => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:13', 'regex:/^\+593[0-9]{9}$/',Rule::unique('users')->ignore($user->id)],
            'cedula'       => ['nullable', 'string', 'max:10', Rule::unique('users')->ignore($user->id)],
        ]);

        if ($request->has('role_id') && $request->role_id) {
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
        // Validar campos requeridos para nueva cédula
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date_format:Y-m-d'],
        ]);
        $user->name = $request->name;
        $user->lastname = $request->lastname;
        $user->birthdate = $request->birthdate;

        if ($request->has('cedula')) {
            $newCedula = $request->cedula;
            
            if (is_null($newCedula)) {
                $user->cedula = null;
                $user->cedula_verified_at = null;
            } elseif ($newCedula !== $user->cedula) {


                // Validación con Registro Civil
                $response = \Illuminate\Support\Facades\Http::asForm()->post('https://si.secap.gob.ec/sisecap/logeo_web/json/busca_persona_registro_civil.php', [
                    'documento' => $newCedula,
                    'tipo'      => '1',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $person = is_array($data) && isset($data[0]) ? $data[0] : $data;

                    if (isset($person['nombres']) && isset($person['apellidos'])) {
                        $apiNombres = preg_replace('/\s+/', ' ', trim(strtoupper($person['nombres'])));
                        $apiApellidos = preg_replace('/\s+/', ' ', trim(strtoupper($person['apellidos'])));
                        $reqName = preg_replace('/\s+/', ' ', trim(strtoupper($request->name)));
                        $reqLastname = preg_replace('/\s+/', ' ', trim(strtoupper($request->lastname)));
                        
                        $apiBirthdate = null;
                        if (isset($person['fechaNacimiento'])) {
                            try {
                                $apiBirthdate = \Carbon\Carbon::createFromFormat('d/m/Y', $person['fechaNacimiento'])->format('Y-m-d');
                            } catch (\Exception $e) {
                                $apiBirthdate = date('Y-m-d', strtotime(str_replace('/', '-', $person['fechaNacimiento'])));
                            }
                        }

                        if ($apiNombres !== $reqName || $apiApellidos !== $reqLastname || ($apiBirthdate && $apiBirthdate !== $request->birthdate)) {
                            return response()->json([
                                'message' => 'Los nombres, apellidos o fecha de nacimiento no coinciden con los datos del Registro Civil para esta cédula',
                            ], 422);
                        }

                        $user->cedula = $newCedula;
                        $user->name = $request->name;
                        $user->lastname = $request->lastname;
                        $user->cedula_verified_at = now();
                        
                        // Actualizar UserInformation
                        $userInfo = $user->userInformation()->firstOrCreate(['user_id' => $user->id]);
                        $userInfo->sexo = $person['sexo'] ?? $userInfo->sexo;
                        $userInfo->birthdate = $request->birthdate;
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
