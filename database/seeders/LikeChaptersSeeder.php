<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LikeChapter;
class LikeChaptersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LikeChapter::create([
            'user_id' => 1,
            'chapter_id' => 1,
        ]);
                LikeChapter::create([
            'user_id' => 1,
            'chapter_id' => 2,
        ]);
        LikeChapter::create([
            'user_id' => 2,
            'chapter_id' => 1,
        ]);
        LikeChapter::create([
            'user_id' => 2,
            'chapter_id' => 2,
        ]);
        LikeChapter::create([
            'user_id' => 2,
            'chapter_id' => 3,
        ]);
                LikeChapter::create([
            'user_id' => 2,
            'chapter_id' => 4,
        ]);
        LikeChapter::create([
            'user_id' => 2,
            'chapter_id' => 5,
        ]);
    }
}
