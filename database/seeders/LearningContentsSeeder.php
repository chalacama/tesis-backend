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
        LearningContent::create([
            'url' => 'https://www.youtube.com/watch?v=-0Fr1blovx8',
            'type_content_id' => 1,
            'chapter_id' => 2,
        ]);
    }
}
