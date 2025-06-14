<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeQuestion;
class TypeQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeQuestion::create([
            'nombre' => 'Opción múltiple',
            'enabled' => true,
        ]);
        TypeQuestion::create([
            'nombre' => 'Varias opciones',
            'enabled' => false,
        ]);
    }
}
