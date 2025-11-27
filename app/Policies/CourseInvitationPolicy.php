<?php

namespace App\Policies;

use App\Models\CourseInvitation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;
class CourseInvitationPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }
    

    // Verificar si el usuario puede invitar colaboradores
    public function inviteCollaborator(User $user, Course $course)
    {
        // El usuario debe ser el dueño del curso
        return $course->tutors()->where('is_owner', true)->where('user_id', $user->id)->exists();
    }

    public function acceptCollaborator(User $user, CourseInvitation $invitation): bool
    {
        // El usuario autenticado debe ser el mismo al que se le envió la invitación.
        return $user->email === $invitation->email;
    }
}
