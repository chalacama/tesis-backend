<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CompletedContent;
use Carbon\Carbon;
class CompletedContentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompletedContent::create([
            'user_id' => 1,
            'learning_content_id' => 1,
            'completed_at' => Carbon::now(),
        ]);
        CompletedContent::create([
            'user_id' => 2,
            'learning_content_id' => 1,
            'completed_at' => Carbon::now(),
        ]);
    }
}
