<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Difficulty;
class DifficultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $difficulties = [
            ['name' => 'Facil'], //beginner
            ['name' => 'Medio'], //intermediate
            ['name' => 'Avanzado'], //advanced
        ];

        foreach ($difficulties as $difficulty) {
            Difficulty::create($difficulty);
        }
    }
}
