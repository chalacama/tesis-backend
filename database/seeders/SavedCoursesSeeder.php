<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SavedCourse;
class SavedCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. User 1 saves Laravel Básico
    SavedCourse::create([
        'user_id' => 1,
        'course_id' => 1, // Laravel Básico
    ]);

    // 2. User 1 saves Marketing Digital Estratégico
    SavedCourse::create([
        'user_id' => 1,
        'course_id' => 3, // Marketing Digital Estratégico
    ]);

    // 3. User 2 saves Laravel Básico
    SavedCourse::create([
        'user_id' => 2,
        'course_id' => 1, // Laravel Básico
    ]);

    // 4. User 2 saves Fundamentos de Diseño Gráfico
    SavedCourse::create([
        'user_id' => 2,
        'course_id' => 2, // Fundamentos de Diseño Gráfico
    ]);

    // 5. User 3 saves Marketing Digital Estratégico
    SavedCourse::create([
        'user_id' => 3,
        'course_id' => 3, // Marketing Digital Estratégico
    ]);

    // 6. User 3 saves Análisis de Datos con Python
    SavedCourse::create([
        'user_id' => 3,
        'course_id' => 6, // Análisis de Datos con Python
    ]);

    // 7. User 4 saves Laravel Básico
    SavedCourse::create([
        'user_id' => 4,
        'course_id' => 1, // Laravel Básico
    ]);

    // 8. User 4 saves Diseño UX/UI
    SavedCourse::create([
        'user_id' => 4,
        'course_id' => 22, // Diseño UX/UI
    ]);

    // 9. User 5 saves Fundamentos de Diseño Gráfico
    SavedCourse::create([
        'user_id' => 5,
        'course_id' => 2, // Fundamentos de Diseño Gráfico
    ]);

    // 10. User 5 saves Inglés Conversacional
    SavedCourse::create([
        'user_id' => 5,
        'course_id' => 8, // Inglés Conversacional
    ]);
    }
}
