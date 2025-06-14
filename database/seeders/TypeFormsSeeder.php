<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeForm;
class TypeFormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeForm::create([
            'nombre' => 'test',
            'max_questions' => 20,
            'enabled' => true,
        ]);
        TypeForm::create([
            'nombre' => 'Evaluacion',
            'max_questions' => 50,
            'enabled' => false,
        ]);
    }
}
