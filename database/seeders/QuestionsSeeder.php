<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionsSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // TEST CAPÍTULO 2 - Instalación y Puesta en Marcha (test_id = 1)
        // =============================================

        Question::create([
            'statement' => '¿Cuál es el comando correcto para crear un nuevo proyecto Laravel usando Composer?',
            'spot' => 1,
            'order' => 1,
            'type_questions_id' => 1,
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Cuáles de los siguientes son requisitos mínimos para instalar Laravel 12?',
            'spot' => 1,
            'order' => 2,
            'type_questions_id' => 2, // Casillas
            'test_id' => 1
        ]);

        // =============================================
        // TEST CAPÍTULO 4 - (Ejemplo: Seeders, Factory, Multimedia o API) (test_id = 2)
        // =============================================

        Question::create([
            'statement' => '¿Qué comando ejecutas para correr todos los seeders de la aplicación?',
            'spot' => 1,
            'order' => 1,
            'type_questions_id' => 1,
            'test_id' => 2
        ]);

        Question::create([
            'statement' => 'En Laravel, ¿cuál es la forma recomendada de subir archivos (imágenes, PDFs, etc.) de manera segura?',
            'spot' => 1,
            'order' => 2,
            'type_questions_id' => 1,
            'test_id' => 2
        ]);

        Question::create([
            'statement' => '¿Cuáles de las siguientes afirmaciones sobre los Factory en Laravel son correctas?',
            'spot' => 1,
            'order' => 3,
            'type_questions_id' => 2,
            'test_id' => 2
        ]);

        Question::create([
            'statement' => '¿Qué método se usa comúnmente para generar un nombre único y seguro al guardar un archivo en storage?',
            'spot' => 1,
            'order' => 4,
            'type_questions_id' => 1,
            'test_id' => 2
        ]);

        Question::create([
            'statement' => 'Al usar el comando php artisan storage:link, ¿qué carpeta pública se crea simbólicamente?',
            'spot' => 1,
            'order' => 5,
            'type_questions_id' => 1,
            'test_id' => 2
        ]);

        // =============================================
        // EXAMEN FINAL - CAPÍTULO 5 (test_id = 3) - 10 preguntas exigentes
        // =============================================

        Question::create([
            'statement' => '¿Cuál es la versión mínima de PHP requerida por Laravel 12?',
            'spot' => 1,
            'order' => 1,
            'type_questions_id' => 1,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Qué comando usarías para crear un controlador de tipo API con métodos resource?',
            'spot' => 1,
            'order' => 2,
            'type_questions_id' => 1,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => 'Selecciona todas las capas que forman parte del patrón MVC en Laravel:',
            'spot' => 1,
            'order' => 3,
            'type_questions_id' => 2,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Cuál de las siguientes sentencias sobre Eloquent es correcta?',
            'spot' => 1,
            'order' => 4,
            'type_questions_id' => 1,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Qué middleware se usa por defecto en las rutas API de Laravel para protegerlas con Sanctum o Passport?',
            'spot' => 1,
            'order' => 5,
            'type_questions_id' => 1,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Cuáles de estos comandos refrescan la base de datos y ejecutan todas las migraciones y seeders?',
            'spot' => 1,
            'order' => 6,
            'type_questions_id' => 2,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => 'En Blade, ¿cuál es la directiva correcta para incluir un componente con slot?',
            'spot' => 1,
            'order' => 7,
            'type_questions_id' => 1,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Qué método de Request se utiliza para validar y redirigir automáticamente con errores si falla?',
            'spot' => 1,
            'order' => 8,
            'type_questions_id' => 1,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Cuáles son ventajas reales de usar Laravel Sanctum frente a Passport en aplicaciones SPA o móviles simples?',
            'spot' => 1,
            'order' => 9,
            'type_questions_id' => 2,
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Cuál es el comando para publicar los archivos de configuración de un paquete (por ejemplo, Laravel Sanctum)?',
            'spot' => 1,
            'order' => 10,
            'type_questions_id' => 1,
            'test_id' => 3
        ]);
    }
}