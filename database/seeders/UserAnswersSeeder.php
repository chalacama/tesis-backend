<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserAnswer;
use Carbon\Carbon;
class UserAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserAnswer::create([
            'answer_id' => 1,
            'user_id' => 1,
            'question_id' => 1,
            'is_correct' => true,
            'answered_at' => Carbon::now(),
        ]);
        UserAnswer::create([
            'answer_id' => 2,
            'user_id' => 2,
            'question_id' => 1,
            'is_correct' => false,
            'answered_at' => Carbon::now(),
        ]);
    }
}
