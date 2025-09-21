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
        return null;
    }
    public function owns(User $user, Course $course): bool
    {
    return $course->tutors()
        ->where('users.id', $user->id)
        ->wherePivot('is_owner', true)
        ->exists();
    }
    public function collaborator(User $user, Course $course): bool
    {
    return $course->tutors()
        ->where('users.id', $user->id)
        ->wherePivot('is_owner', false)
        ->exists();
    }
    /**
     * Determina si el usuario puede ver la lista de cursos en el panel de gestión.
     * (Reemplaza la lógica de tu método getAllCourses)
     */
    public function viewAnyHidden(User $user): bool
    {
        
        return $user->hasRole('tutor');
    }
    public function viewHidden(User $user, Course $course): bool
    {
    return $this->owns($user, $course);
    }
    

    public function viewAny(User $user): bool
    {
        return $user && $user->hasPermissionTo('course.read');
        
    }
    public function view(User $user, Course $course): bool
    {
        return $user && $user->hasPermissionTo('course.read');
    }
    /**
     * Determina si el usuario puede crear cursos.
     * (Reemplaza la lógica de tu método createCourse)
     */
    public function create(User $user): bool
    {
        // Un usuario puede crear un curso si tiene el permiso.
        // El rol 'student' no lo tiene, así que esto funciona.
        return $user->hasPermissionTo('course.create');
    }

    /**
     * Determina si el usuario puede actualizar un curso específico.
     * ¡ESTA ES LA LÓGICA CLAVE QUE TE FALTA!
     */
    public function update(User $user, Course $course): bool
    {
        return $this->owns($user, $course);
    }
    /**
     * Determina si el usuario puede eliminar un curso específico.
     */
    public function delete(User $user, Course $course): bool
    {
    // Solo el dueño puede eliminar
    return $this->owns($user, $course);
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
