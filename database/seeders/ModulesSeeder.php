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
        // 1. Laravel Básico (Course ID 1) - 3 modules
    Module::create([
        'name' => 'Introducción a Laravel',
        'order' => 1,
        'course_id' => 1,
    ]);
    Module::create([
        'name' => 'Rutas y Controladores',
        'order' => 2,
        'course_id' => 1,
    ]);
    Module::create([
        'name' => 'Eloquent ORM',
        'order' => 3,
        'course_id' => 1,
    ]);

    // 2. Fundamentos de Diseño Gráfico (Course ID 2) - 3 modules
    Module::create([
        'name' => 'Principios del Diseño',
        'order' => 1,
        'course_id' => 2,
    ]);
    Module::create([
        'name' => 'Herramientas de Diseño',
        'order' => 2,
        'course_id' => 2,
    ]);
    Module::create([
        'name' => 'Creación de Composiciones',
        'order' => 3,
        'course_id' => 2,
    ]);

    // 3. Marketing Digital Estratégico (Course ID 3) - 3 modules
    Module::create([
        'name' => 'Fundamentos de SEO',
        'order' => 1,
        'course_id' => 3,
    ]);
    Module::create([
        'name' => 'Publicidad en Redes Sociales',
        'order' => 2,
        'course_id' => 3,
    ]);
    Module::create([
        'name' => 'Análisis de Campañas',
        'order' => 3,
        'course_id' => 3,
    ]);

    // 4. Introducción a la Inteligencia Artificial (Course ID 5) - 3 modules
    Module::create([
        'name' => 'Conceptos Básicos de IA',
        'order' => 1,
        'course_id' => 5,
    ]);
    Module::create([
        'name' => 'Algoritmos de Machine Learning',
        'order' => 2,
        'course_id' => 5,
    ]);
    Module::create([
        'name' => 'Aplicaciones Prácticas de IA',
        'order' => 3,
        'course_id' => 5,
    ]);

    // 5. Análisis de Datos con Python (Course ID 6) - 3 modules
    Module::create([
        'name' => 'Introducción a Python',
        'order' => 1,
        'course_id' => 6,
    ]);
    Module::create([
        'name' => 'Análisis de Datos con Pandas',
        'order' => 2,
        'course_id' => 6,
    ]);
    Module::create([
        'name' => 'Visualización de Datos',
        'order' => 3,
        'course_id' => 6,
    ]);

    // 6. Finanzas Personales (Course ID 7) - 2 modules
    Module::create([
        'name' => 'Gestión de Presupuestos',
        'order' => 1,
        'course_id' => 7,
    ]);
    Module::create([
        'name' => 'Introducción a Inversiones',
        'order' => 2,
        'course_id' => 7,
    ]);

    // 7. Inglés Conversacional (Course ID 8) - 2 modules
    Module::create([
        'name' => 'Vocabulario Básico',
        'order' => 1,
        'course_id' => 8,
    ]);
    Module::create([
        'name' => 'Conversación Práctica',
        'order' => 2,
        'course_id' => 8,
    ]);

    // 8. Fotografía Digital (Course ID 9) - 2 modules
    Module::create([
        'name' => 'Técnicas de Fotografía',
        'order' => 1,
        'course_id' => 9,
    ]);
    Module::create([
        'name' => 'Edición de Imágenes',
        'order' => 2,
        'course_id' => 9,
    ]);

    // 9. Edición de Video Profesional (Course ID 10) - 2 modules
    Module::create([
        'name' => 'Fundamentos de Edición',
        'order' => 1,
        'course_id' => 10,
    ]);
    Module::create([
        'name' => 'Efectos y Postproducción',
        'order' => 2,
        'course_id' => 10,
    ]);

    // 10. Yoga y Mindfulness (Course ID 11) - 2 modules
    Module::create([
        'name' => 'Técnicas de Yoga',
        'order' => 1,
        'course_id' => 11,
    ]);
    Module::create([
        'name' => 'Prácticas de Mindfulness',
        'order' => 2,
        'course_id' => 11,
    ]);
    }
}
