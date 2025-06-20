<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Answer;
class AnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Answer::create([
            'option' => 'París',
            'is_correct' => true,
            'order' => 1,
            'question_id' => 1,
        ]);
        Answer::create([
            'option' => 'Londres',
            'order' => 2,
            'is_correct' => false,
            'question_id' => 1,
        ]);
    }
}
