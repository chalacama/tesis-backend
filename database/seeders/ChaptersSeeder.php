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
// id 6, 7, 8, 9, 10, 11
Chapter::create([
    'title' => 'Imagen de referencia',
    'description' => 'Imagen de referencia para el proyecto',
    'module_id' => 2,
    'order' => 5,
]);
Chapter::create([
    'title' => 'Archivo editable word ',
    'description' => 'Word editable',
    'module_id' => 2,
    'order' => 1,
]);
Chapter::create([
    'title' => 'Archivo editable pptx',
    'description' => 'PowerPoint editable',
    'module_id' => 2,
    'order' => 2,
]);
Chapter::create([
    'title' => 'Archivo editable excel',
    'description' => 'Excel editable',
    'module_id' => 2,
    'order' => 3,
]);
Chapter::create([
    'title' => 'Archivo comprimido',
    'description' => 'Archivo comprimido con recursos adicionales',
    'module_id' => 2,
    'order' => 4,
]);
Chapter::create([
    'title' => 'Audio de referencia',
    'description' => 'Audio de referencia para el proyecto',
    'module_id' => 2,
    'order' => 6,
]);
//id: 12
Chapter::create([
    'title' => 'Archivo de texto',
    'description' => 'Archivo de texto con información adicional',
    'module_id' => 2,
    'order' => 7,
]);
//id: 13
Chapter::create([
    'title' => 'Video de google drive',
    'description' => 'Video alojado en Google Drive',
    'module_id' => 3,
    'order' => 1,
]);
Chapter::create([
    'title' => 'Audio de google drive',
    'description' => 'Audio alojado en Google Drive',
    'module_id' => 3,
    'order' => 2,
]);
Chapter::create([
    'title' => 'Pdf de google drive',
    'description' => 'PDF alojado en Google Drive',
    'module_id' => 3,
    'order' => 3,
]);
Chapter::create([
    'title' => 'Word de google drive',
    'description' => 'Word alojado en Google Drive',
    'module_id' => 3,
    'order' => 4,
]);
Chapter::create([
    'title' => 'PowerPoint de google drive',
    'description' => 'PowerPoint alojado en Google Drive',
    'module_id' => 3,
    'order' => 5,
]);
Chapter::create([
    'title' => 'Excel de google drive',
    'description' => 'Excel alojado en Google Drive',
    'module_id' => 3,
    'order' => 6,
]);
Chapter::create([
    'title' => 'Archivo comprimido de google drive',
    'description' => 'Archivo comprimido alojado en Google Drive',
    'module_id' => 3,
    'order' => 7,
]);
Chapter::create([
    'title' => 'Archivo de texto de google drive',
    'description' => 'Archivo de texto alojado en Google Drive',
    'module_id' => 3,
    'order' => 8,
]);
//id: 21
Chapter::create([
    'title' => 'video de onedrive',
    'description' => 'Archivo de texto alojado en OneDrive',
    'module_id' => 5,
    'order' => 1,
]);
Chapter::create([
    'title' => 'Audio de texto de one drive',
    'description' => 'Audio de texto alojado en One Drive',
    'module_id' => 5,
    'order' => 2,
]);
Chapter::create([
    'title' => 'Pdf de texto de onedrive',
    'description' => 'Pdf de texto alojado en One Drive',
    'module_id' => 5,
    'order' => 3,
]);
Chapter::create([
    'title' => 'Word de texto de onedrive',
    'description' => 'Word de texto alojado en One Drive',
    'module_id' => 5,
    'order' => 4,
]);
Chapter::create([
    'title' => 'PowerPoint de texto de onedrive',
    'description' => 'PowerPoint de texto alojado en One Drive',
    'module_id' => 5,
    'order' => 5,
]);
Chapter::create([   
    'title' => 'Excel de onedrive',
    'description' => 'Excel de texto alojado en One Drive',
    'module_id' => 5,
    'order' => 6,
]);     
Chapter::create([
    'title' => 'Archivo comprimido de onedrive',
    'description' => 'Archivo comprimido de texto alojado en One Drive',
    'module_id' => 5,
    'order' => 7,
]);
Chapter::create([
    'title' => 'Archivo de texto de onedrive',
    'description' => 'Archivo de texto alojado en One Drive',
    'module_id' => 5,
    'order' => 8,
]);

}
}
