<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // id : 1
        Course::create([
            'title' => 'Laravel Básico',
            'description' => 'Curso introductorio a Laravel.',
            
            'enabled' => true,
           
            
            'archived_at' => null,
            'published_at' =>'2025-06-22 10:30:00'
        ]);
        // id : 2
        Course::create([
            'title' => 'Angular Básico',
            'description' => 'Curso introductorio a Angular.',
            'enabled' => true,
            'archived_at' => '2025-05-22 10:30:00',
            'published_at' =>'2025-06-25 10:30:00'
        ]);
        // id : 3
        Course::create([
            'title' => 'PHP Avanzado',
            'description' => 'Curso avanzado de PHP.',
            'enabled' => true,
            'archived_at' => null,
            'published_at' =>'2025-08-15 10:30:00'
        ]);
        // id : 4
        Course::create([
            'title' => 'Java Avanzado',
            'description' => 'Curso avanzado de Java.',
            'enabled' => false,
            'archived_at' => '2025-07-22 10:30:00',
            'published_at' =>'2025-06-30 10:30:00'
        ]);
    }
}
