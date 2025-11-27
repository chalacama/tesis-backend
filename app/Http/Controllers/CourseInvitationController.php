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
    // Verificar autorización usando el Policy (dueño o admin)
    $this->authorize('update', $course);

    // 1. Validación básica + que el email exista en users
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ], [
        'email.exists' => 'El correo debe pertenecer a un usuario registrado.',
    ]);

    $invitedEmail = $request->input('email');

    // 2. Buscar usuario invitado (ya sabemos que existe)
    $invitedUser = User::where('email', $invitedEmail)->first();

    // 3. Debe ser tutor o admin
    if (!$invitedUser->hasAnyRole(['tutor', 'admin'])) {
        return response()->json([
            'message' => 'Solo se puede invitar a usuarios con rol tutor o admin.',
        ], 422);
    }

    // 4. No puede ser ya dueño del curso (ignora archivados gracias a tutors()->wherePivotNull)
    $isOwner = $course->owner()
        ->where('users.id', $invitedUser->id)
        ->exists();

    // 5. No puede ser ya colaborador del curso
    $isCollaborator = $course->collaborators()
        ->where('users.id', $invitedUser->id)
        ->exists();

    if ($isOwner || $isCollaborator) {
        return response()->json([
            'message' => 'Este usuario ya forma parte del curso como dueño o colaborador.',
        ], 422);
    }

    // 6. Slot de colaborador: solo 1 colaborador o 1 invitación pendiente
    $existingCollaborator = $course->collaborators()->first();

    $pendingInvitation = $course->invitations()
        ->where('status', 'pending')
        ->first();

    if ($existingCollaborator || $pendingInvitation) {
        return response()->json([
            'message' => 'Ya existe un colaborador o una invitación pendiente para este curso.',
        ], 422);
    }

    // 7. Crear la invitación
    $invitation = $course->invitations()->create([
        'user_id' => $request->user()->id, // quién invita
        'email'   => $invitedEmail,
        'token'   => Str::random(40) . time(),
        'status'  => 'pending',
    ]);

    // 8. Enviar el correo electrónico de invitación
    Mail::to($invitedEmail)->send(new TutorInvitationEmail($invitation));

    // 9. Notificación interna con token + course_id + mensaje
    if ($invitedUser) {
        $invitedUser->notify(new TutorInvitationNotification($invitation));
    }

    return response()->json([
        'message'    => 'Invitación enviada correctamente.',
        'invitation' => $invitation,
    ], 201);
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
