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
            'name' => 'pm4',
            'max_size' => '500MB',
            'min_duration_seconds' => '60',
            'max_duration_seconds' => '3600',
            'enabled' => true,
        ]);
        TypeLearningContent::create([
            'name' => 'youtube-watch',
            'max_size' => '',
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => true,
        ]);
        TypeLearningContent::create([
            'name' => 'youtube-shorts',
            'max_size' => '50MB',
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => true,
        ]);
        TypeLearningContent::create([
            'name' => 'pdf',
            'max_size' => '50MB',
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => false,
        ]);
    }
}
