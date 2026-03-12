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
            'url' => 'https://res.cloudinary.com/dvvqko1vv/video/upload/v1764845297/archives/chapter/1.mp4',
            'type_content_id' => 2,
            'chapter_id' => 1,
            'format_id' => 2, // mp4
            'size_mb' => 500.00,
            'duration_seconds' => 126,
        ]);
        LearningContent::create([
            'url' => 'https://www.youtube.com/watch?v=_Rsen6614Dg',
            'type_content_id' => 1,
            'chapter_id' => 2,
            'format_id' => 1, // youtube
            'duration_seconds' => 383,
            'size_mb' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1764846842/archives/chapter/3.pdf',
            'type_content_id' => 2,
            'chapter_id' => 3,
            'format_id' => 3, // pdf
            'size_mb' => 3.84,
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1763338367/archives/chapter/4.tmp',
            'type_content_id' => 2,
            'chapter_id' => 6,
            'format_id' => 4, // word
            'size_mb' => 0.0366,
            'duration_seconds' => null,
        ]);

        
    }
}
