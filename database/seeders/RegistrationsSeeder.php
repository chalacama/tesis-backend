<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Registration;
class RegistrationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. User 1 approved in 2 courses (Laravel Básico and Desarrollo Web Full Stack)
    Registration::create([
        
        'user_id' => 1,
        'course_id' => 1, // Laravel Básico
    ]);
    Registration::create([
        
        'user_id' => 1,
        'course_id' => 2, // Fundamentos de Diseño Gráfico
    ]);

    // 2. User 2 with annulment in one course (Marketing Digital Estratégico)
    Registration::create([
        
        'user_id' => 2,
        'course_id' => 3, // Marketing Digital Estratégico
    ]);

    // 3. User 2 in another course (to balance registrations)
    Registration::create([
        
        'user_id' => 2,
        'course_id' => 1, // Laravel Básico (popular course)
    ]);

    // 4. User 3 in Laravel Básico (popular course)
    Registration::create([
        
        'user_id' => 3,
        'course_id' => 1, // Laravel Básico
    ]);

    // 5. User 3 in Análisis de Datos con Python
    Registration::create([
        
        'user_id' => 3,
        'course_id' => 6, // Análisis de Datos con Python
    ]);

    // 6. User 4 in Marketing Digital Estratégico (popular course)
    Registration::create([
        
        'user_id' => 4,
        'course_id' => 3, // Marketing Digital Estratégico
    ]);

    // 7. User 4 in Diseño UX/UI
    Registration::create([
        
        'user_id' => 4,
        'course_id' => 22, // Diseño UX/UI
    ]);

    // 8. User 5 in Laravel Básico (popular course)
    Registration::create([
        
        'user_id' => 5,
        'course_id' => 1, // Laravel Básico
    ]);

    // 9. User 5 in Inglés Conversacional
    Registration::create([
        
        'user_id' => 5,
        'course_id' => 8, // Inglés Conversacional
    ]);

    // 10. User 6 in Marketing Digital Estratégico (popular course)
    Registration::create([
        
        'user_id' => 6,
        'course_id' => 3, // Marketing Digital Estratégico
    ]);

    // 11. User 6 in Fotografía Digital
    Registration::create([
        
        'user_id' => 6,
        'course_id' => 9, // Fotografía Digital
    ]);

    // 12. User 7 in Desarrollo Web Full Stack (popular course)
    Registration::create([
       
        
        'user_id' => 7,
        'course_id' => 2, // Fundamentos de Diseño Gráfico
    ]);

    // 13. User 7 in Liderazgo Efectivo
    Registration::create([
       
        'user_id' => 7,
        'course_id' => 18, // Liderazgo Efectivo
    ]);

    // 14. User 8 in Laravel Básico (popular course)
    Registration::create([
        
        'user_id' => 8,
        'course_id' => 1, // Laravel Básico
    ]);

    // 15. User 8 in Análisis de Big Data
    Registration::create([
       
        'user_id' => 8,
        'course_id' => 25, // Análisis de Big Data
    ]);
    }
}
