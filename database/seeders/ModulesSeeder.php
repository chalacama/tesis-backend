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
        Module::create([
            'name' => 'Introducción a Laravel',
            'order' => 1,
            'enabled' => true,
            'course_id' => 1, // Asegúrate de que el curso con ID 1 exista
        ]);
        Module::create([
            'name' => 'Eloquent ORM',
            'order' => 2,
            'enabled' => true,
            'course_id' => 1,
        ]);
        Module::create([
            'name' => 'Introducción a Vue.js',
            'order' => 1,
            'enabled' => true,
            'course_id' => 2,
        ]);
    }
}
