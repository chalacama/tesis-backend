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
        Course::create([
            'title' => 'Laravel Básico',
            'description' => 'Curso introductorio a Laravel.',
            'is_certified' => true,
            'enabled' => true,
            'archived_at' => null,
        ]);
        Course::create([
            'title' => 'PHP Avanzado',
            'description' => 'Curso avanzado de PHP.',
            'is_certified' => false,
            'enabled' => true,
            'archived_at' => null,
        ]);
    }
}
