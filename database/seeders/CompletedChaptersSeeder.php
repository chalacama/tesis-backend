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
        CompletedChapter::create([
            'user_id' => 1,
            'chapter_id' => 1,
            'content_at' => Carbon::now(),
            'test_at' => Carbon::now(),
        ]);
        CompletedChapter::create([
            'user_id' => 2,
            'chapter_id' => 1,
            'content_at' => null,
            'test_at' => null,
        ]);
        CompletedChapter::create([
            'user_id' => 2,
            'chapter_id' => 2,
            'content_at' => null,
            'test_at' => null,
        ]);
        CompletedChapter::create([
            'user_id' => 2,
            'chapter_id' => 3,
            'content_at' => null,
            'test_at' => Carbon::now(),
        ]);
        CompletedChapter::create([
            'user_id' => 2,
            'chapter_id' => 3,
            'content_at' => Carbon::now(),
            'test_at' => Carbon::now(),
        ]);
        CompletedChapter::create([
            'user_id' => 2,
            'chapter_id' => 4,
            'content_at' => Carbon::now(),
            'test_at' => Carbon::now(),
        ]);
    }
}
