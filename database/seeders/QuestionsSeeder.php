<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;
class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Question::create([
            'statement' => '¿Cuál es la capital de Francia?',
            'spot' => 1,
            'type_questions_id' => 1,
            'chapter_id' => 1
        ]);
        Question::create([
            'statement' => 'Explica el ciclo de vida de una petición HTTP.',
            'spot' => 1.5,
            'type_questions_id' => 1,
            'chapter_id' => 2
        ]);
    }
}
