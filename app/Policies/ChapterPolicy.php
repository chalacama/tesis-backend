<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Chapter;
use App\Models\Registration;
use App\Models\Module;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChapterPolicy
{
    use HandlesAuthorization;

    /**
     * Admin ve todo.
     */
    /* public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    } */

    /**
     * Ver el CONTENIDO del capítulo.
     */
    public function viewChapter(User $user, Chapter $chapter): bool
    {
        // Cargar curso + módulo
        $chapter->loadMissing(
            'module:id,course_id,order',
            'module.course:id,enabled,private'
        );

        $course = $chapter->module->course;

        // 1) Curso debe estar habilitado
        if (!$course || !$course->enabled) {
            return false;
        }

        // 2) Si es el capítulo introductorio → lo puede ver
        if ($this->isIntro($chapter)) {
            // Reglas aquí:
            // ❍ Si quieres que se pueda ver sin registro pero con login:
            return true; // ya está autenticado porque type-hint es User
        }

        // 3) Para el resto de capítulos → debe estar registrado
        return Registration::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determina si es el capítulo introductorio:
     * primer módulo del curso con menor 'order'
     * y primer capítulo de ese módulo con menor 'order'
     */
    private function isIntro(Chapter $chapter): bool
    {
        $chapter->loadMissing('module:id,course_id,order');

        $courseId = $chapter->module->course_id;

        // Buscar el módulo con MENOR 'order'
        $firstModuleId = Module::where('course_id', $courseId)
            ->orderBy('order', 'asc')
            ->value('id');

        if (!$firstModuleId) {
            return false;
        }

        // Buscar el capítulo con MENOR 'order' dentro de ese módulo
        $firstChapterId = Chapter::where('module_id', $firstModuleId)
            ->orderBy('order', 'asc')
            ->value('id');

        // ES intro si es exactamente ese capítulo
        return (int)$chapter->id === (int)$firstChapterId;
    }
}
