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
            'url' => 'https://www.youtube.com/shorts/-qr9dUuOzJc',
            'type_content_id' => 3,
            'chapter_id' => 1,
        ]);
        LearningContent::create([
            'url' => 'https://www.youtube.com/watch?v=-0Fr1blovx8',
            'type_content_id' => 2,
            'chapter_id' => 2,
        ]);
    }
}
