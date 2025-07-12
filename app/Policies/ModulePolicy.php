<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ModulePolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('tutor');
    }

    public function view(User $user, Module $module): bool
    {
        // El tutor solo puede ver módulos de cursos asignados
        return $user->hasRole('tutor') && $module->course->tutors()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('modules.create');
    }

    public function update(User $user, Module $module): bool
    {
        return $user->hasPermissionTo('modules.update') && $module->course->tutors()->where('users.id', $user->id)->exists();
    }

    public function activate(User $user, Module $module): bool
    {
        return $this->update($user, $module);
    }

    public function delete(User $user, Module $module): bool
    {
        return $user->hasPermissionTo('modules.delete') && $module->course->tutors()->where('users.id', $user->id)->exists();
    }    
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Module $module): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Module $module): bool
    {
        return false;
    }
}
