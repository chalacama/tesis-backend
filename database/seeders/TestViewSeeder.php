<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TestView;
class TestViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TestView::create([
            'user_id' => 1,
            'chapter_id' => 1,
            
        ]);
        TestView::create([
            'user_id' => 2,
            'chapter_id' => 2,
            
        ]);


    }
}
