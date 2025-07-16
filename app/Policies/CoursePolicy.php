<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Este método se ejecuta antes que cualquier otro en la policy.
     * Si el usuario es 'admin', se le concede acceso a todo inmediatamente.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null; // Devuelve null para que se ejecute el método específico (update, delete, etc.)
    }

    /**
     * Determina si el usuario puede ver la lista de cursos en el panel de gestión.
     * (Reemplaza la lógica de tu método getAllCourses)
     */
    public function viewAnyHidden(User $user): bool
    {
        // Solo administradores y tutores pueden ver la lista de cursos del backend.
        return $user->hasRole('tutor'); // El admin ya fue aprobado por el método before()
    }
    public function viewHidden(User $user, Course $course): bool
    {
    // Admin ya tiene acceso por el método before
    // Tutor puede ver si está asignado
    return $user->hasRole('tutor') && $course->tutors()->where('users.id', $user->id)->exists();
    }
    public function viewAny(User $user): bool
    {
        return $user && $user->hasPermissionTo('courses.read');
        
    }
    public function view(User $user, Course $course): bool
    {
    // Admin ya tiene acceso por el método before
    // Tutor puede ver si está asignado
    // return $user->hasRole('tutor') && $course->tutors()->where('users.id', $user->id)->exists();
    }
    /**
     * Determina si el usuario puede crear cursos.
     * (Reemplaza la lógica de tu método createCourse)
     */
    public function create(User $user): bool
    {
        // Un usuario puede crear un curso si tiene el permiso.
        // El rol 'student' no lo tiene, así que esto funciona.
        return $user->hasPermissionTo('courses.create');
    }

    /**
     * Determina si el usuario puede actualizar un curso específico.
     * ¡ESTA ES LA LÓGICA CLAVE QUE TE FALTA!
     */
    public function update(User $user, Course $course): bool
    {
        // Un usuario puede actualizar un curso si:
        // 1. Tiene el permiso general 'courses.update' Y
        // 2. Es uno de los tutores asignados a ESE curso.
        return $user->hasPermissionTo('courses.update') && $course->tutors()->where('users.id', $user->id)->exists();
    }
    /**
     * Determina si el usuario puede eliminar un curso específico.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->hasPermissionTo('courses.delete') && $course->tutors()->where('users.id', $user->id)->exists();
    }
    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Module $module): bool
    {
        return false;
    }
    public function restore(User $user, Module $module): bool
    {
        return false;
    }
    
}
