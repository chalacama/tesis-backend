<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContentView;
use Carbon\Carbon;
class ContentViewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContentView::create([
            'user_id' => 1,
            'learning_content_id' => 1,
            'second_seen' => '130',
        ]);
        ContentView::create([
            'user_id' => 1,
            'learning_content_id' => 2,
            'second_seen' => '120',
        ]);
    }
}
