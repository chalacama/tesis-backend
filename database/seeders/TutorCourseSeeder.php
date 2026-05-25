<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TutorCourse;

class TutorCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
    // 1. Laravel Básico (Owner: User 2, Collaborator: User 3)
    TutorCourse::create(['course_id' => 1, 'user_id' => 2, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 1, 'user_id' => 3, 'is_owner' => false]);

    // 2. Fundamentos de Diseño Gráfico (Owner: User 3)
    TutorCourse::create(['course_id' => 2, 'user_id' => 3, 'is_owner' => true]);

    // 3. Marketing Digital Estratégico (Owner: User 1, Collaborator: User 2)
    TutorCourse::create(['course_id' => 3, 'user_id' => 1, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 3, 'user_id' => 2, 'is_owner' => false]);

    // 4. Emprendimiento 101 (Owner: User 2)
    TutorCourse::create(['course_id' => 4, 'user_id' => 2, 'is_owner' => true]);

    // 5. Introducción a la Inteligencia Artificial (Owner: User 2, Collaborator: User 1)
    TutorCourse::create(['course_id' => 5, 'user_id' => 2, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 5, 'user_id' => 1, 'is_owner' => false]);

    // 6. Análisis de Datos con Python (Owner: User 1)
    TutorCourse::create(['course_id' => 6, 'user_id' => 1, 'is_owner' => true]);

    // 7. Finanzas Personales (Owner: User 3)
    TutorCourse::create(['course_id' => 7, 'user_id' => 3, 'is_owner' => true]);

    // 8. Inglés Conversacional (Owner: User 3)
    TutorCourse::create(['course_id' => 8, 'user_id' => 3, 'is_owner' => true]);

    // 9. Fotografía Digital (Owner: User 3, Collaborator: User 2)
    TutorCourse::create(['course_id' => 9, 'user_id' => 3, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 9, 'user_id' => 2, 'is_owner' => false]);

    // 10. Edición de Video Profesional (Owner: User 1)
    TutorCourse::create(['course_id' => 10, 'user_id' => 1, 'is_owner' => true]);

    // 11. Yoga y Mindfulness (Owner: User 2)
    TutorCourse::create(['course_id' => 11, 'user_id' => 2, 'is_owner' => true]);

    // 12. Producción Musical (Owner: User 3, Collaborator: User 2)
    TutorCourse::create(['course_id' => 12, 'user_id' => 3, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 12, 'user_id' => 2, 'is_owner' => false]);

    // 13. Escritura Creativa (Owner: User 3)
    TutorCourse::create(['course_id' => 13, 'user_id' => 3, 'is_owner' => true]);

    // 14. Pedagogía Moderna (Owner: User 2)
    TutorCourse::create(['course_id' => 14, 'user_id' => 2, 'is_owner' => true]);

    // 15. Cocina Internacional (Owner: User 1, Collaborator: User 3)
    TutorCourse::create(['course_id' => 15, 'user_id' => 1, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 15, 'user_id' => 3, 'is_owner' => false]);

            // --- Cursos sin dueño asignados al Admin (User 1) ---

        // 16. Pintura al Óleo (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 16, 'user_id' => 1, 'is_owner' => true]);

        // 17. Sostenibilidad Empresarial (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 17, 'user_id' => 1, 'is_owner' => true]);

        // 18. Liderazgo Efectivo (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 18, 'user_id' => 1, 'is_owner' => true]);

        // 19. Ingeniería de Software (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 19, 'user_id' => 1, 'is_owner' => true]);

        // 20. Fundamentos de Biología (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 20, 'user_id' => 1, 'is_owner' => true]);

        // 21. JavaScript Avanzado (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 21, 'user_id' => 1, 'is_owner' => true]);

        // 22. Diseño UX/UI (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 22, 'user_id' => 1, 'is_owner' => true]);

        // 23. Publicidad en Redes Sociales (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 23, 'user_id' => 1, 'is_owner' => true]);

        // 24. Gestión de Startups (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 24, 'user_id' => 1, 'is_owner' => true]);

        // 25. Análisis de Big Data (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 25, 'user_id' => 1, 'is_owner' => true]);

        // 26. Fotografía de Retrato (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 26, 'user_id' => 1, 'is_owner' => true]);

        // 27. Producción de Podcasts (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 27, 'user_id' => 1, 'is_owner' => true]);

        // 28. Nutrición Básica (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 28, 'user_id' => 1, 'is_owner' => true]);

        // 29. Teoría Musical (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 29, 'user_id' => 1, 'is_owner' => true]);

        // 30. Física Básica (Owner: User 1 - Admin)
        TutorCourse::create(['course_id' => 30, 'user_id' => 1, 'is_owner' => true]);
    }
}
