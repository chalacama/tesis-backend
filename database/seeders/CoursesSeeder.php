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
            'is_certified' => true,
            'enabled' => true,
            'max_attempts' => 3,
            'is_unlimited' => false,
            'archived_at' => null,
            'published_at' =>null
        ]);
        // id : 2
        Course::create([
            'title' => 'Angular Básico',
            'description' => 'Curso introductorio a Angular.',
            'is_certified' => false,
            'enabled' => true,
            'max_attempts' => 5,
            'is_unlimited' => true,
            'archived_at' => null,
            'published_at' =>null
        ]);
        // id : 3
        Course::create([
            'title' => 'PHP Avanzado',
            'description' => 'Curso avanzado de PHP.',
            'is_certified' => false,
            'enabled' => true,
            'max_attempts' => 2,
            'is_unlimited' => true,
            'archived_at' => null,
            'published_at' =>null
        ]);
        // id : 4
        Course::create([
            'title' => 'Java Avanzado',
            'description' => 'Curso avanzado de Java.',
            'is_certified' => true,
            'enabled' => false,
            'max_attempts' => 5,
            'is_unlimited' => false,
            'archived_at' => null,
            'published_at' =>null
        ]);
    }
}
