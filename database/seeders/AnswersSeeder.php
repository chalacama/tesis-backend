<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Answer;

class AnswersSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // Pregunta 1 - Capítulo 2
        // =============================================
        $q1 = 1;
        Answer::create(['option' => 'composer create-project laravel/laravel nombre-proyecto', 'is_correct' => true,  'order' => 1, 'question_id' => $q1]);
        Answer::create(['option' => 'laravel new nombre-proyecto', 'is_correct' => false, 'order' => 2, 'question_id' => $q1]);
        Answer::create(['option' => 'php artisan new nombre-proyecto', 'is_correct' => false, 'order' => 3, 'question_id' => $q1]);
        Answer::create(['option' => 'composer global require laravel/installer', 'is_correct' => false, 'order' => 4, 'question_id' => $q1]);

        // Pregunta 2 - Capítulo 2 (casillas)
        $q2 = 2;
        Answer::create(['option' => 'PHP >= 8.2', 'is_correct' => true,  'order' => 1, 'question_id' => $q2]);
        Answer::create(['option' => 'Extensión BCMath', 'is_correct' => true,  'order' => 2, 'question_id' => $q2]);
        Answer::create(['option' => 'Extensión OpenSSL', 'is_correct' => true,  'order' => 3, 'question_id' => $q2]);
        Answer::create(['option' => 'MySQL 5.6 o inferior', 'is_correct' => false, 'order' => 4, 'question_id' => $q2]);
        Answer::create(['option' => 'Composer', 'is_correct' => true,  'order' => 5, 'question_id' => $q2]);

        // =============================================
        // Preguntas Capítulo 4
        // =============================================
        $q3 = 3;
        Answer::create(['option' => 'php artisan db:seed', 'is_correct' => true,  'order' => 1, 'question_id' => $q3]);
        Answer::create(['option' => 'php artisan migrate --seed', 'is_correct' => false, 'order' => 2, 'question_id' => $q3]);
        Answer::create(['option' => 'php artisan seed:run', 'is_correct' => false, 'order' => 3, 'question_id' => $q3]);

        $q4 = 4;
        Answer::create(['option' => 'Usar $request->file(\'photo\')->store(\'photos\')', 'is_correct' => true,  'order' => 1, 'question_id' => $q4]);
        Answer::create(['option' => 'Guardar directamente en public/uploads', 'is_correct' => false, 'order' => 2, 'question_id' => $q4]);
        Answer::create(['option' => 'Usar el disco "public" y storage:link', 'is_correct' => true,  'order' => 3, 'question_id' => $q4]);

        $q5 = 5;
        Answer::create(['option' => 'Los factories reemplazan completamente a los seeders', 'is_correct' => false, 'order' => 1, 'question_id' => $q5]);
        Answer::create(['option' => 'Se definen en database/factories', 'is_correct' => true,  'order' => 2, 'question_id' => $q5]);
        Answer::create(['option' => 'Usan Faker para generar datos realistas', 'is_correct' => true,  'order' => 3, 'question_id' => $q5]);
        Answer::create(['option' => 'Se llaman desde los seeders con Model::factory()->count(50)->create()', 'is_correct' => true,  'order' => 4, 'question_id' => $q5]);

        $q6 = 6;
        Answer::create(['option' => '$request->file(\'avatar\')->storeAs(\'avatars\', $filename)', 'is_correct' => false, 'order' => 1, 'question_id' => $q6]);
        Answer::create(['option' => 'Str::uuid() o $file->hashName()', 'is_correct' => true,  'order' => 2, 'question_id' => $q6]);
        Answer::create(['option' => '$request->file(\'avatar\')->move(public_path(), $name)', 'is_correct' => false, 'order' => 3, 'question_id' => $q6]);

        $q7 = 7;
        Answer::create(['option' => 'storage/app/public → public/storage', 'is_correct' => true,  'order' => 1, 'question_id' => $q7]);
        Answer::create(['option' => 'storage/app → public', 'is_correct' => false, 'order' => 2, 'question_id' => $q7]);

        // =============================================
        // EXAMEN FINAL (10 preguntas)
        // =============================================
        $q8 = 8;
        Answer::create(['option' => 'PHP 8.2', 'is_correct' => true,  'order' => 1, 'question_id' => $q8]);
        Answer::create(['option' => 'PHP 8.1', 'is_correct' => false, 'order' => 2, 'question_id' => $q8]);
        Answer::create(['option' => 'PHP 8.3', 'is_correct' => false, 'order' => 3, 'question_id' => $q8]);

        $q9 = 9;
        Answer::create(['option' => 'php artisan make:controller Api/UserController --api', 'is_correct' => true,  'order' => 1, 'question_id' => $q9]);
        Answer::create(['option' => 'php artisan make:controller UserController --resource', 'is_correct' => false, 'order' => 2, 'question_id' => $q9]);

        $q10 = 10;
        Answer::create(['option' => 'Model', 'is_correct' => true,  'order' => 1, 'question_id' => $q10]);
        Answer::create(['option' => 'View', 'is_correct' => true,  'order' => 2, 'question_id' => $q10]);
        Answer::create(['option' => 'Controller', 'is_correct' => true,  'order' => 3, 'question_id' => $q10]);
        Answer::create(['option' => 'Router', 'is_correct' => false, 'order' => 4, 'question_id' => $q10]);

        $q11 = 11;
        Answer::create(['option' => 'Eloquent es un Query Builder', 'is_correct' => false, 'order' => 1, 'question_id' => $q11]);
        Answer::create(['option' => 'Eloquent implementa el patrón Active Record', 'is_correct' => true,  'order' => 2, 'question_id' => $q11]);
        Answer::create(['option' => 'Los modelos Eloquent heredan de Illuminate\\Database\\Eloquent\\Model', 'is_correct' => true,  'order' => 3, 'question_id' => $q11]);

        $q12 = 12;
        Answer::create(['option' => 'auth:sanctum', 'is_correct' => true,  'order' => 1, 'question_id' => $q12]);
        Answer::create(['option' => 'auth:api', 'is_correct' => false, 'order' => 2, 'question_id' => $q12]);

        $q13 = 13;
        Answer::create(['option' => 'php artisan migrate:fresh --seed', 'is_correct' => true,  'order' => 1, 'question_id' => $q13]);
        Answer::create(['option' => 'php artisan db:refresh', 'is_correct' => false, 'order' => 2, 'question_id' => $q13]);
        Answer::create(['option' => 'php artisan migrate --seed', 'is_correct' => false, 'order' => 3, 'question_id' => $q13]);

        $q14 = 14;
        Answer::create(['option' => '@component / @endcomponent', 'is_correct' => false, 'order' => 1, 'question_id' => $q14]);
        Answer::create(['option' => '<x-alert> contenido </x-alert>', 'is_correct' => true,  'order' => 2, 'question_id' => $q14]);

        $q15 = 15;
        Answer::create(['option' => '$request->validate([...])', 'is_correct' => true,  'order' => 1, 'question_id' => $q15]);
        Answer::create(['option' => 'Validator::make()', 'is_correct' => false, 'order' => 2, 'question_id' => $q15]);

        $q16 = 16;
        Answer::create(['option' => 'Es más ligero y simple que Passport', 'is_correct' => true,  'order' => 1, 'question_id' => $q16]);
        Answer::create(['option' => 'Usa tokens de larga duración (SPA tokens)', 'is_correct' => true,  'order' => 2, 'question_id' => $q16]);
        Answer::create(['option' => 'No requiere OAuth completo', 'is_correct' => true,  'order' => 3, 'question_id' => $q16]);
        Answer::create(['option' => 'Requiere cliente y servidor separados', 'is_correct' => false, 'order' => 4, 'question_id' => $q16]);

        $q17 = 17;
        Answer::create(['option' => 'php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"', 'is_correct' => true,  'order' => 1, 'question_id' => $q17]);
        Answer::create(['option' => 'php artisan sanctum:publish', 'is_correct' => false, 'order' => 2, 'question_id' => $q17]);
    }
}