<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LikeLearningContent;
class LikeLearningContentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LikeLearningContent::create([
            'user_id' => 1,
            'learning_contents_id' => 1,
        ]);
        LikeLearningContent::create([
            'user_id' => 2,
            'learning_contents_id' => 1,
        ]);
    }
}
