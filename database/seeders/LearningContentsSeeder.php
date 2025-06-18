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
            'enabled' => true,
            'type_content_id' => 3, // Asegúrate de que el tipo exista
        ]);
        LearningContent::create([
            'url' => 'https://www.youtube.com/watch?v=-0Fr1blovx8',
            'enabled' => true,
            'type_content_id' => 2,
        ]);
    }
}
