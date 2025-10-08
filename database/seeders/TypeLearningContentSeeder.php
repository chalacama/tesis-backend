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
            'name' => 'youtube',
            'max_size_mb' => null,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
        ]);
        TypeLearningContent::create([
            'name' => 'archivo',
            'max_size_mb' => 100.00,
            'min_duration_seconds' => 120,
            'max_duration_seconds' => 480,
        ]);

    }
}
