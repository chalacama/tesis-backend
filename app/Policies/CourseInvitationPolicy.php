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
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CourseInvitation $courseInvitation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CourseInvitation $courseInvitation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CourseInvitation $courseInvitation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CourseInvitation $courseInvitation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CourseInvitation $courseInvitation): bool
    {
        return false;
    }
    // Verificar si el usuario puede invitar colaboradores
    public function inviteCollaborator(User $user, Course $course)
    {
        // El usuario debe ser el dueño del curso
        return $course->tutors()->where('is_owner', true)->where('user_id', $user->id)->exists();
    }
    public function accept(User $user, CourseInvitation $invitation): bool
    {
        // El usuario autenticado debe ser el mismo al que se le envió la invitación.
        return $user->email === $invitation->email;
    }
}
