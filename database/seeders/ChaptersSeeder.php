<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chapter;
class ChaptersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Course 1: Laravel Básico (Module IDs 1, 2, 3) - 3 modules, 7 chapters
    // Module 1: Introducción a Laravel
    Chapter::create([
        'title' => '¿Qué es Laravel?',
        'description' => 'Video introductorio sobre el framework Laravel.',
        'module_id' => 1,
        'order' => 1,
    ]);
    Chapter::create([
    'title' => 'Instalación y Puesta en Marcha 🛠️',
    'description' => 'Guía paso a paso para preparar tu entorno de desarrollo profesional. Aprenderás a instalar Laravel mediante Composer, verificar los requisitos de PHP y desplegar tu primer servidor local para ver el proyecto en vivo.',
    'module_id' => 1,
    'order' => 2,
]);
Chapter::create([
    'title' => 'Configuración y Variables de Entorno ⚙️',
    'description' => 'Domina el archivo .env, el corazón de la configuración en Laravel. En esta lección configuraremos la conexión a la base de datos, gestionaremos las credenciales de seguridad y exploraremos la estructura de carpetas moderna del framework.',
    'module_id' => 1,
    'order' => 3,
]);
Chapter::create([
    'title' => 'Test del modulo',
    'description' => 'Test del modulo 1',
    'module_id' => 1,
    'order' => 4,
]);
Chapter::create([
    'title' => 'Examen final del curso',
    'description' => 'Test del modulo 1',
    'module_id' => 4,
    'order' => 4,
]);
    
    }
}
