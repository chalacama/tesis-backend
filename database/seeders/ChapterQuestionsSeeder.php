<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChapterQuestion;
class ChapterQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChapterQuestion::create([
            'order' => 1,
            'chapter_id' => 1,
            'question_id' => 1,
        ]);
        ChapterQuestion::create([
            'order' => 2,
            'chapter_id' => 1,
            'question_id' => 2,
        ]);
    }
}
