<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Form;
class FormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Form::create([
            'title' => 'Encuesta de satisfacción',
            'order' => 1,
            'random_questions' => false,
            'enabled' => true,
            'type_form_id' => 1, // Asegúrate de que el tipo exista
        ]);
        Form::create([
            'title' => 'Examen final',
            'order' => 2,
            'random_questions' => true,
            'enabled' => true,
            'type_form_id' => 2,
        ]);
    }
}
