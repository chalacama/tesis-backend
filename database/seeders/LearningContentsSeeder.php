<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LearningContent;
class LearningContentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/video/upload/v1759810999/archives/chapter/1.mp4',
            'type_content_id' => 2,
            'chapter_id' => 1,
        ]);
        /* LearningContent::create([
            'url' => 'https://www.youtube.com/watch?v=-0Fr1blovx8',
            'type_content_id' => 1,
            'chapter_id' => 2,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1763264314/archives/chapter/3.pdf',
            'type_content_id' => 2,
            'chapter_id' => 3,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1763338367/archives/chapter/4.tmp',
            'type_content_id' => 2,
            'chapter_id' => 4,
        ]); */
    }
}
