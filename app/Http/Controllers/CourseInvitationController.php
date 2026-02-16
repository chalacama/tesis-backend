<?php

namespace App\Http\Controllers;

use App\Models\CourseInvitation;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use App\Models\TutorCourse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TutorInvitationEmail;
use App\Notifications\TutorInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\EducationalUnit;
use Exception;

class CourseInvitationController extends Controller
{
    use AuthorizesRequests;


    public function show(Request $request, Course $course) 
{
    // admin, dueño y colaborador pueden ver esta información
    $this->authorize('leave', $course);

    $user = $request->user();

    // Dueño (solo uno)
    $owner = $course->owner()->first();

    // Colaborador (solo uno según tu regla)
    $collaborator = $course->collaborators()->first();

    // Invitación pendiente (colaborador) – SIN type
    $pendingInvitation = $course->invitations()
        ->where('status', 'pending')
        ->first();

    // Si hay invitación pendiente, buscamos el usuario invitado por email
    $invitedUser = null;
    if ($pendingInvitation) {
        $invitedUser = User::where('email', $pendingInvitation->email)->first();
    }

    // Slot del colaborador: o usuario, o invitación, o null
    $collaboratorSlot = null;

    if ($collaborator) {
        $collaboratorSlot = [
            'is_invitation' => false,
            'user' => [
                'id'                  => $collaborator->id,
                'name'                => $collaborator->name,
                'lastname'            => $collaborator->lastname,
                'username'            => $collaborator->username,
                'email'               => $collaborator->email,
                'profile_picture_url' => $collaborator->profile_picture_url,
            ],
        ];
    } elseif ($pendingInvitation) {
        $collaboratorSlot = [
            'is_invitation' => true,
            'invitation' => [
                'id'         => $pendingInvitation->id,
                'email'      => $pendingInvitation->email,
                'status'     => $pendingInvitation->status,
                'created_at' => $pendingInvitation->created_at,
                'user'       => $invitedUser ? [
                    'id'                  => $invitedUser->id,
                    'name'                => $invitedUser->name,
                    'lastname'            => $invitedUser->lastname,
                    'username'            => $invitedUser->username,
                    'profile_picture_url' => $invitedUser->profile_picture_url,
                ] : null,
            ],
        ];
    }

    // ¿Tiene permiso de edición? (admin o dueño del curso)
    $canEdit = $user->can('update', $course);

    // Solo se puede invitar si:
    //  - tiene permiso de edición
    //  - no hay colaborador
    //  - no hay invitación pendiente
    $canInviteCollaborator = $canEdit && !$collaborator && !$pendingInvitation;

    return response()->json([
        'course_id' => $course->id,
        'owner' => $owner ? [
            'id'                  => $owner->id,
            'name'                => $owner->name,
            'lastname'            => $owner->lastname,
            'username'            => $owner->username,
            'email'               => $owner->email,
            'profile_picture_url' => $owner->profile_picture_url,
        ] : null,
        'collaborator_slot'       => $collaboratorSlot,
        'can_invite_collaborator' => $canInviteCollaborator,
        'can_edit'                => $canEdit,
    ]);
}



public function validate(Request $request)
{
        $data = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $query       = $data['query'];
        $currentUser = $request->user();

        $users = User::query()
            ->where('id', '!=', $currentUser->id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['tutor', 'admin']);
            })
            ->where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%")
                  ->orWhere('lastname', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get([
                'id',
                'name',
                'lastname',
                'username',
                'profile_picture_url',
                'email',
            ]);

        return response()->json([
            'results' => $users,
        ]);
}
    
 
    
public function deleteCollaborator(Request $request, Course $course, User $user)
{
    // Solo el dueño del curso o un admin deberían poder hacer esto.
    // Asumo que tu CoursePolicy@update ya controla eso.
    $this->authorize('update', $course);

    // Buscar el registro en la tabla pivote tutor_courses
    $tutorCourse = TutorCourse::where('course_id', $course->id)
        ->where('user_id', $user->id)
        ->first();

    if (!$tutorCourse) {
        return response()->json([
            'message' => 'Este usuario no está registrado como tutor de este curso.'
        ], 404);
    }

    // Proteger al dueño: este método es solo para colaboradores
    if ($tutorCourse->is_owner) {
        return response()->json([
            'message' => 'No puedes eliminar al dueño del curso con este método.'
        ], 422);
    }

    // Soft delete gracias a SoftDeletes en TutorCourse
    $tutorCourse->delete();

    return response()->json([
        'message' => 'Colaborador eliminado correctamente.'
    ], 200);
}

public function deleteOwner(Request $request, Course $course, User $user)
{
    // Admin que está haciendo la acción (ya viene con el permiso en el middleware)
    $admin = $request->user();

    // 1. Verificar que el usuario pasado en la ruta sea realmente el dueño actual
    $ownerPivot = TutorCourse::where('course_id', $course->id)
        ->where('user_id', $user->id)
        ->where('is_owner', true)
        ->first();

    if (!$ownerPivot) {
        return response()->json([
            'message' => 'El usuario indicado no es el dueño actual de este curso.'
        ], 404);
    }

    // (Opcional pero sano) evitar que el admin se quite a sí mismo
    if ($admin->id === $user->id) {
        return response()->json([
            'message' => 'No puedes eliminarte a ti mismo como dueño con esta acción.'
        ], 422);
    }

    DB::transaction(function () use ($course, $user, $admin) {
        // 2. Eliminar relación del antiguo dueño (soft delete en tutor_courses)
        TutorCourse::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->delete();

        // 3. Asegurar que ningún otro tutor quede marcado como dueño
        TutorCourse::where('course_id', $course->id)
            ->update(['is_owner' => false]);

        // 4. Hacer al admin el nuevo dueño del curso
        $adminPivot = TutorCourse::where('course_id', $course->id)
            ->where('user_id', $admin->id)
            ->first();

        if ($adminPivot) {
            // Ya estaba como tutor/colaborador → ahora pasa a ser dueño
            $adminPivot->update(['is_owner' => true]);
        } else {
            // No estaba asociado al curso → se crea como dueño directamente
            TutorCourse::create([
                'course_id' => $course->id,
                'user_id'   => $admin->id,
                'is_owner'  => true,
            ]);
        }
    });

    return response()->json([
        'message'   => 'El dueño del curso ha sido cambiado correctamente.',
        'new_owner' => [
            'id'                  => $admin->id,
            'name'                => $admin->name,
            'lastname'            => $admin->lastname,
            'username'            => $admin->username,
            'email'               => $admin->email,
            'profile_picture_url' => $admin->profile_picture_url,
        ],
    ], 200);
}

public function leave(Request $request, Course $course)
{
    $user = $request->user();

    // Policy: debe ser dueño o colaborador del curso (o admin por before)
    $this->authorize('leave', $course);

    // Buscar relación en la tabla pivote tutor_courses
    $tutorCourse = TutorCourse::where('course_id', $course->id)
        ->where('user_id', $user->id)
        ->first();

    if (!$tutorCourse) {
        return response()->json([
            'message' => 'No estás asociado a este curso como tutor.'
        ], 404);
    }

    // 🧩 CASO 1: Es COLABORADOR → puede salir normal
    if (!$tutorCourse->is_owner) {
        $tutorCourse->delete(); // Soft delete

        return response()->json([
            'message' => 'Has salido del curso correctamente.'
        ], 200);
    }

    // 🧩 CASO 2: Es DUEÑO → debe haber colaborador para entregarle el curso
    $collaborator = TutorCourse::where('course_id', $course->id)
        ->where('is_owner', false)
        ->first();

    if (!$collaborator) {
        return response()->json([
            'message' => 'No puedes salir del curso porque no hay ningún colaborador al que transferir la propiedad.'
        ], 422);
    }

    DB::transaction(function () use ($tutorCourse, $collaborator) {
        // 1. El colaborador pasa a ser el nuevo dueño
        $collaborator->update(['is_owner' => true]);

        // 2. El antiguo dueño sale del curso
        $tutorCourse->delete();
    });

    return response()->json([
        'message' => 'Has salido del curso y el colaborador ahora es el nuevo dueño.',
        'new_owner' => [
            'id'                  => $collaborator->user->id,
            'name'                => $collaborator->user->name,
            'lastname'            => $collaborator->user->lastname,
            'username'            => $collaborator->user->username,
            'email'               => $collaborator->user->email,
            'profile_picture_url' => $collaborator->user->profile_picture_url,
        ],
    ], 200);
}


public function change(Request $request, Course $course)
{
    // Solo dueño del curso o admin (via CoursePolicy@update + before)
    $this->authorize('update', $course);

    // Buscar dueño actual
    $ownerPivot = TutorCourse::where('course_id', $course->id)
        ->where('is_owner', true)
        ->first();

    if (!$ownerPivot) {
        return response()->json([
            'message' => 'No se encontró un dueño asignado para este curso.'
        ], 404);
    }

    // Buscar colaborador actual
    $collaboratorPivot = TutorCourse::where('course_id', $course->id)
        ->where('is_owner', false)
        ->first();

    if (!$collaboratorPivot) {
        return response()->json([
            'message' => 'No existe un colaborador para intercambiar la propiedad del curso.'
        ], 422);
    }

    DB::transaction(function () use ($ownerPivot, $collaboratorPivot) {
        // El dueño pasa a colaborador
        $ownerPivot->update(['is_owner' => false]);

        // El colaborador pasa a dueño
        $collaboratorPivot->update(['is_owner' => true]);
    });

    // Recargamos relaciones de usuario para responder bonito
    $newOwner       = $collaboratorPivot->user()->first();
    $newCollaborator = $ownerPivot->user()->first();

    return response()->json([
        'message' => 'Los roles de dueño y colaborador han sido intercambiados correctamente.',
        'owner' => $newOwner ? [
            'id'                  => $newOwner->id,
            'name'                => $newOwner->name,
            'lastname'            => $newOwner->lastname,
            'username'            => $newOwner->username,
            'email'               => $newOwner->email,
            'profile_picture_url' => $newOwner->profile_picture_url,
        ] : null,
        'collaborator' => $newCollaborator ? [
            'id'                  => $newCollaborator->id,
            'name'                => $newCollaborator->name,
            'lastname'            => $newCollaborator->lastname,
            'username'            => $newCollaborator->username,
            'email'               => $newCollaborator->email,
            'profile_picture_url' => $newCollaborator->profile_picture_url,
        ] : null,
    ], 200);
}


public function cancel(Request $request, Course $course, CourseInvitation $invitation)
{
    // Solo dueño del curso o admin
    // (CourseInvitationPolicy::before da acceso a admin, y inviteCollaborator exige que sea dueño)
    $this->authorize('update', $course);

    // Verificar que la invitación pertenezca a este curso
    if ($invitation->course_id !== $course->id) {
        return response()->json([
            'message' => 'La invitación no pertenece a este curso.'
        ], 404);
    }

    // Solo se pueden cancelar invitaciones pendientes
    if ($invitation->status !== 'pending') {
        return response()->json([
            'message' => 'Esta invitación ya ha sido procesada y no puede ser cancelada.'
        ], 422);
    }

    // Marcar como expirada (cancelada)
    $invitation->update([
        'status' => 'expired',
    ]);

    return response()->json([
        'message' => 'La invitación ha sido cancelada correctamente (expirada).'
    ], 200);
}

public function store(Request $request, Course $course)
    {
        set_time_limit(300);
        $this->authorize('update', $course);

        // 1. Validaciones (Mantenemos tu lógica)
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'El correo debe pertenecer a un usuario registrado.',
        ]);

        $invitedEmail = $request->input('email');
        $invitedUser = User::where('email', $invitedEmail)->first();

        // Validaciones de roles y existencia previa (Mantenemos tu lógica intacta)
        if (!$invitedUser->hasAnyRole(['tutor', 'admin'])) {
            return response()->json(['message' => 'Solo se puede invitar a usuarios con rol tutor o admin.'], 422);
        }

        $isOwner = $course->owner()->where('users.id', $invitedUser->id)->exists();
        $isCollaborator = $course->collaborators()->where('users.id', $invitedUser->id)->exists();

        if ($isOwner || $isCollaborator) {
            return response()->json(['message' => 'Este usuario ya forma parte del curso.'], 422);
        }

        $existingCollaborator = $course->collaborators()->first();
        $pendingInvitation = $course->invitations()->where('status', 'pending')->first();

        if ($existingCollaborator || $pendingInvitation) {
            return response()->json(['message' => 'Ya existe un colaborador o invitación pendiente.'], 422);
        }

        // 2. CREAR LA INVITACIÓN EN BD
        $invitation = $course->invitations()->create([
            'user_id' => $request->user()->id,
            'email'   => $invitedEmail,
            'token'   => Str::random(40) . time(),
            'status'  => 'pending',
        ]);

        // 3. NOTIFICACIÓN WEB (LO MÁS IMPORTANTE)
        // La ejecutamos ANTES del correo o independientemente de si falla.
        if ($invitedUser) {
            try {
                $invitedUser->notify(new TutorInvitationNotification($invitation));
            } catch (Exception $e) {
                Log::error("Error al crear notificación en base de datos: " . $e->getMessage());
                // Incluso si falla la notificación, intentamos enviar el correo.
            }
        }

        // 4. ENVÍO DE CORREO "A PRUEBA DE FALLOS"
        // Usamos try-catch para que si falla, NO rompa la respuesta al usuario.
        
        // Determinamos el dominio
        $domain = substr(strrchr($invitedEmail, "@"), 1);
        $isInstitutional = EducationalUnit::where('organization_domain', $domain)->exists();
        
        // Configuraciones base para los intentos
        $gmailConfig = [
            'mailer' => 'gmail',
            'from'   => env('GMAIL_FROM_ADDRESS'),
            'name'   => env('GMAIL_FROM_NAME')
        ];
        
        $outlookConfig = [
            'mailer' => 'outlook',
            'from'   => env('OUTLOOK_FROM_ADDRESS'),
            'name'   => env('OUTLOOK_FROM_NAME')
        ];

        try {
            if ($isInstitutional || $domain === 'espam.edu.ec') {
                // === LÓGICA INSTITUCIONAL (INTENTO DOBLE) ===
                
                // Intento 1: Outlook
                try {
                    Mail::mailer('outlook')
                        ->to($invitedEmail)
                        ->send(new TutorInvitationEmail($invitation, $outlookConfig));
                    
                    Log::info("Correo institucional enviado vía Outlook a $invitedEmail");
                    
                } catch (Exception $eOutlook) {
                    Log::error("Fallo envío Outlook a institucional ($invitedEmail): " . $eOutlook->getMessage());
                    
                    // Intento 2: Gmail (Fallback inmediato)
                    try {
                        Mail::mailer('gmail')
                            ->to($invitedEmail)
                            ->send(new TutorInvitationEmail($invitation, $gmailConfig));
                            
                        Log::info("Correo institucional enviado vía Gmail (Respaldo) a $invitedEmail");
                        
                    } catch (Exception $eGmail) {
                        Log::error("Fallo CRÍTICO: Ni Outlook ni Gmail funcionaron para $invitedEmail. " . $eGmail->getMessage());
                        // Aquí no hacemos nada más, el código sigue para retornar éxito al frontend
                    }
                }

            } elseif (in_array($domain, ['outlook.com', 'hotmail.com', 'live.com', 'outlook.es'])) {
                // === LÓGICA OUTLOOK PURO ===
                Mail::mailer('outlook')
                    ->to($invitedEmail)
                    ->send(new TutorInvitationEmail($invitation, $outlookConfig));
            } else {
                // === LÓGICA GMAIL (POR DEFECTO PARA GMAIL.COM Y OTROS) ===
                Mail::mailer('gmail')
                    ->to($invitedEmail)
                    ->send(new TutorInvitationEmail($invitation, $gmailConfig));
            }

        } catch (Exception $e) {
            // Este catch captura errores de los bloques 'else' (gmail.com o outlook.com puros)
            // O errores generales no capturados arriba.
            Log::error("Error general enviando correo a $invitedEmail: " . $e->getMessage());
            // NO lanzamos el error, permitimos que continúe.
        }

        // 5. RESPUESTA EXITOSA
        // Al llegar aquí, la notificación en BD ya está creada (paso 3) y el correo se intentó enviar.
        return response()->json([
            'message'    => 'Invitación generada correctamente.',
            'invitation' => $invitation,
        ], 201);
    }
//     public function store(Request $request, Course $course)
// {
//     // Aumentar tiempo de ejecución si es necesario (aunque Resend es muy rápido)
//     set_time_limit(300);
    
//     $this->authorize('update', $course);

//     // 1. Validaciones
//     $request->validate([
//         'email' => 'required|email|exists:users,email',
//     ], [
//         'email.exists' => 'El correo debe pertenecer a un usuario registrado.',
//     ]);

//     $invitedEmail = $request->input('email');
//     $invitedUser = User::where('email', $invitedEmail)->first();

//     // Validar roles
//     if (!$invitedUser->hasAnyRole(['tutor', 'admin'])) {
//         return response()->json(['message' => 'Solo se puede invitar a usuarios con rol tutor o admin.'], 422);
//     }

//     // Validar si ya es parte del curso
//     $isOwner = $course->owner()->where('users.id', $invitedUser->id)->exists();
//     $isCollaborator = $course->collaborators()->where('users.id', $invitedUser->id)->exists();

//     if ($isOwner || $isCollaborator) {
//         return response()->json(['message' => 'Este usuario ya forma parte del curso.'], 422);
//     }

//     // Validar invitaciones pendientes o colaboradores existentes
//     $existingCollaborator = $course->collaborators()->first();
//     $pendingInvitation = $course->invitations()->where('status', 'pending')->first();

//     if ($existingCollaborator || $pendingInvitation) {
//         return response()->json(['message' => 'Ya existe un colaborador o invitación pendiente.'], 422);
//     }

//     // 2. CREAR LA INVITACIÓN EN BD
//     $invitation = $course->invitations()->create([
//         'user_id' => $request->user()->id,
//         'email'   => $invitedEmail,
//         'token'   => Str::random(40) . time(),
//         'status'  => 'pending',
//     ]);

//     // 3. NOTIFICACIÓN WEB
//     if ($invitedUser) {
//         try {
//             $invitedUser->notify(new TutorInvitationNotification($invitation));
//         } catch (Exception $e) {
//             Log::error("Error al crear notificación en base de datos: " . $e->getMessage());
//         }
//     }

//     // 4. ENVÍO DE CORREO CON RESEND (Laravel 12 Standard)
//     try {
//         // Ya no especificamos 'gmail' u 'outlook'. Usamos el default del .env (MAIL_MAILER=resend)
//         // Pasamos un array vacío [] como config, para que tu Mailable use los defaults del .env
//         Mail::to($invitedEmail)->send(new TutorInvitationEmail($invitation, []));
        
//         Log::info("Invitación enviada vía RESEND a: $invitedEmail");

//     } catch (Exception $e) {
//         // Capturamos el error para no romper la experiencia del usuario, pero lo logueamos
//         Log::error("Error enviando correo con RESEND a $invitedEmail: " . $e->getMessage());
        
//         // Opcional: Podrías retornar un warning en el JSON si el correo falló, 
//         // pero la invitación en BD ya existe.
//     }

//     // 5. RESPUESTA EXITOSA
//     return response()->json([
//         'message'    => 'Invitación generada correctamente.',
//         'invitation' => $invitation,
//     ], 201);
// }
/**
     * 🧠 Cerebro de Selección de Correo Inteligente
     * Decide qué servidor SMTP usar basándose en el dominio del destinatario.
     */
private function getSmartMailerAndSender(string $email): array
    {
        // 1. Extraer el dominio (ej: gmail.com, espam.edu.ec)
        $domain = substr(strrchr($email, "@"), 1);

        // 2. Configuración por defecto (usaremos Gmail como respaldo)
        $mailer = 'gmail';
        $fromEmail = env('GMAIL_FROM_ADDRESS');
        $fromName  = env('GMAIL_FROM_NAME');

        // 3. Lógica para GMAIL
        if ($domain === 'gmail.com') {
            return [
                'mailer' => 'gmail',
                'from'   => env('GMAIL_FROM_ADDRESS'),
                'name'   => env('GMAIL_FROM_NAME')
            ];
        }

        // 4. Lógica para OUTLOOK / HOTMAIL / LIVE
        if (in_array($domain, ['outlook.com', 'hotmail.com', 'live.com', 'outlook.es'])) {
            return [
                'mailer' => 'outlook',
                'from'   => env('OUTLOOK_FROM_ADDRESS'),
                'name'   => env('OUTLOOK_FROM_NAME')
            ];
        }

        // Busca la función getSmartMailerAndSender y cambia el bloque institucional por esto:

// 5. Lógica INSTITUCIONAL (Base de Datos)
$isInstitutional = EducationalUnit::where('organization_domain', $domain)->exists();

if ($isInstitutional) {
    // AHORA SÍ: Usamos Outlook para correos institucionales
    return [
        'mailer' => 'outlook', // <--- CAMBIADO A OUTLOOK
        'from'   => env('OUTLOOK_FROM_ADDRESS'), // Asegura usar el email de outlook
        'name'   => env('OUTLOOK_FROM_NAME')     // O "DigiMentor para {$domain}" si prefieres
    ];
}

        // Retorno por defecto (Gmail)
        return [
            'mailer' => $mailer,
            'from'   => $fromEmail,
            'name'   => $fromName
        ];
    }


public function accept(Request $request)
{
    $request->validate(['token' => 'required|string']);

    // 1. Buscar la invitación por token
    $invitation = CourseInvitation::where('token', $request->token)->first();

    if (!$invitation) {
        return response()->json(['message' => 'El token de invitación no es válido.'], 404);
    }

    if ($invitation->status !== 'pending') {
        return response()->json(['message' => 'Esta invitación ya ha sido procesada.'], 422);
    }

    // 2. Revisar si el usuario existe (por email de la invitación)
    $user = User::where('email', $invitation->email)->first();

    // 3. Manejar el caso del usuario nuevo
    if (!$user) {
        return response()->json([
            'status'  => 'user_not_found',
            'message' => 'No existe un usuario con este email. Por favor, regístrate primero.',
            'email'   => $invitation->email,
        ], 200);
    }

    // 🔹 Cargar relaciones necesarias: curso + miniatura + quien invitó
    $invitation->load(['course.miniature', 'inviter']);

    // 4. Adjuntar al curso como colaborador (sin autenticación, basado en el email)
    DB::transaction(function () use ($user, $invitation) {
        $course = $invitation->course;

        // Evitar duplicados si por alguna razón ya es tutor
        $alreadyTutor = $course->tutors()
            ->where('users.id', $user->id)
            ->exists();

        if (!$alreadyTutor) {
            $course->tutors()->attach($user->id, ['is_owner' => false]);
        }

        $invitation->update(['status' => 'accepted']);
    });

    // 🔹 Construir respuesta enriquecida
    $course  = $invitation->course;
    $inviter = $invitation->inviter; // puede ser null si algo raro pasó

    return response()->json([
        'message' => 'Invitación aceptada correctamente. Ya eres colaborador del curso.',

        'course' => [
            'id'            => $course->id,
            'title'         => $course->title,
            'miniature_url' => optional($course->miniature)->url,
        ],

        'invited_user' => [
            'id'                   => $user->id,
            'name'                 => $user->name,
            'lastname'             => $user->lastname,
            'username'             => $user->username,
            'profile_picture_url'  => $user->profile_picture_url,
        ],

        'inviter_user' => $inviter ? [
            'id'                   => $inviter->id,
            'name'                 => $inviter->name,
            'lastname'             => $inviter->lastname,
            'username'             => $inviter->username,
            'profile_picture_url'  => $inviter->profile_picture_url,
        ] : null,
    ], 200);
}

}
