<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Chapter;
use App\Models\Registration;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;
class ChapterPolicy
{
    /**
     * Ver el CONTENIDO del capítulo (no solo la estructura).
     * Regla:
     * - El curso debe estar activo.
     * - El usuario debe tener permiso course.read (igual que CoursePolicy->view).
     * - Debe estar registrado en el curso, EXCEPTO si el capítulo es el "intro" (order=1 del primer módulo con order=1).
     */
    public function viewChapter(User $user, Chapter $chapter): bool
    {
        // Cargar lo mínimo necesario
        $chapter->loadMissing('module:id,course_id,order', 'module.course:id,enabled,private');

        $course = $chapter->module->course;

        // 1) Permiso base (mismo criterio que usarías para ver la estructura del curso)
        if (!$user->can('course.read')) {
            return false;
        }

        // 2) Curso activo
        if (!$course || !$course->enabled) {
            return false;
        }

        // 3) ¿Capítulo introductorio? (primer módulo y primer capítulo)
        $isIntro = $this->isIntro($chapter);

        if ($isIntro) {
            // Intro: usuario logueado con permiso puede verlo sin registro
            return true;
        }

        // 4) Para el resto: debe estar registrado
        return Registration::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    

    /** Determina si es capítulo introductorio (primer módulo y primer capítulo) */
    private function isIntro(Chapter $chapter): bool
    {
        // Necesitamos el order del módulo y del capítulo
        $chapter->loadMissing('module:id,course_id,order');

        // Por performance: asume que en BD el capítulo con order=1 dentro del módulo con order=1 es "intro"
        return (int)$chapter->module->order === 1 && (int)$chapter->order === 1;
    }
}
