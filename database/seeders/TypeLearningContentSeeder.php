<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeLearningContent;
class TypeLearningContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeLearningContent::create([
            'name' => 'cloudinary',
            'max_size_mb' => 500.00,
            'min_duration_seconds' => 120,
            'max_duration_seconds' => 480,
            'enabled' => true,
        ]);
        TypeLearningContent::create([
            'name' => 'youtube-watch',
            'max_size_mb' => null,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => true,
        ]);
        TypeLearningContent::create([
            'name' => 'youtube-shorts',
            'max_size_mb' => null,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => true,
        ]);
        TypeLearningContent::create([
            'name' => 'pdf',
            'max_size_mb' => 100.00,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => false,
        ]);
        /* TypeLearningContent::create([
            'name' => 'cloudinary',
            'max_size' => '500MB',
            'min_duration_seconds' => '120',
            'max_duration_seconds' => '480',
            'enabled' => true,
        ]); */
    }
}
