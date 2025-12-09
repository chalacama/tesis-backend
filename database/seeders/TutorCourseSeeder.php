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

    // 3. Marketing Digital Estratégico (Owner: User 4, Collaborator: User 5)
    TutorCourse::create(['course_id' => 3, 'user_id' => 4, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 3, 'user_id' => 5, 'is_owner' => false]);

    // 4. Emprendimiento 101 (Owner: User 5)
    TutorCourse::create(['course_id' => 4, 'user_id' => 5, 'is_owner' => true]);

    // 5. Introducción a la Inteligencia Artificial (Owner: User 2, Collaborator: User 4)
    TutorCourse::create(['course_id' => 5, 'user_id' => 2, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 5, 'user_id' => 4, 'is_owner' => false]);

    // 6. Análisis de Datos con Python (Owner: User 4)
    TutorCourse::create(['course_id' => 6, 'user_id' => 4, 'is_owner' => true]);

    // 7. Finanzas Personales (Owner: User 5)
    TutorCourse::create(['course_id' => 7, 'user_id' => 5, 'is_owner' => true]);

    // 8. Inglés Conversacional (Owner: User 3)
    TutorCourse::create(['course_id' => 8, 'user_id' => 3, 'is_owner' => true]);

    // 9. Fotografía Digital (Owner: User 3, Collaborator: User 2)
    TutorCourse::create(['course_id' => 9, 'user_id' => 3, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 9, 'user_id' => 2, 'is_owner' => false]);

    // 10. Edición de Video Profesional (Owner: User 4)
    TutorCourse::create(['course_id' => 10, 'user_id' => 4, 'is_owner' => true]);

    // 11. Yoga y Mindfulness (Owner: User 5)
    TutorCourse::create(['course_id' => 11, 'user_id' => 5, 'is_owner' => true]);

    // 12. Producción Musical (Owner: User 3, Collaborator: User 2)
    TutorCourse::create(['course_id' => 12, 'user_id' => 3, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 12, 'user_id' => 2, 'is_owner' => false]);

    // 13. Escritura Creativa (Owner: User 3)
    TutorCourse::create(['course_id' => 13, 'user_id' => 3, 'is_owner' => true]);

    // 14. Pedagogía Moderna (Owner: User 5)
    TutorCourse::create(['course_id' => 14, 'user_id' => 5, 'is_owner' => true]);

    // 15. Cocina Internacional (Owner: User 5, Collaborator: User 4)
    TutorCourse::create(['course_id' => 15, 'user_id' => 5, 'is_owner' => true]);
    TutorCourse::create(['course_id' => 15, 'user_id' => 4, 'is_owner' => false]);
    }
}
