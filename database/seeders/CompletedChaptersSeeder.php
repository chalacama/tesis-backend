<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CompletedChapter;
use Carbon\Carbon;
class CompletedChaptersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // capitulo 1 compleado por user 1
        CompletedChapter::create([
            'user_id' => 1,
            'chapter_id' => 1,
            'content_progress' => 100,
            'question_id' => 2, // id de la pregunta que tiene que responder
            'content_at' => Carbon::now(),
            'test_at' => Carbon::now(),
        ]);
        // capitulo 1 compleado por user 2
        CompletedChapter::create([
            'user_id' => 2,
            'chapter_id' => 1,
            'content_progress' => 0,
            'question_id' => null,
            'content_at' => null,
            'test_at' => null,
        ]);
        CompletedChapter::create([
            'user_id' => 2,
            'chapter_id' => 2,
            'content_progress' => 10,
            'question_id' => null,
            'content_at' => null,
            'test_at' => null,
        ]);
        CompletedChapter::create([
            'user_id' => 2,
            'chapter_id' => 3,
            'content_progress' => 75,
            'question_id' => null,
            'content_at' => Carbon::now(),
            'test_at' => Carbon::now(),
        ]);
        
    }
}
