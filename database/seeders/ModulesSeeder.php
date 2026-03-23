<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;
class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Laravel Básico (Course ID 1)
    Module::create([
        'name' => 'Introducción a Laravel',
        'order' => 1,
        'course_id' => 1,
    ]);
    Module::create([
        'name' => 'Desarrollo api rest',
        'order' => 2,
        'course_id' => 1,
    ]);
    Module::create([
        'name' => 'google Drive',
        'order' => 3,
        'course_id' => 1,
    ]);
    Module::create([
        'name' => 'Evaluación',
        'order' => 5,
        'course_id' => 1,
    ]);
    Module::create([
        'name' => 'OneDrive',
        'order' => 4,
        'course_id' => 1,
    ]);
    
    }
}
