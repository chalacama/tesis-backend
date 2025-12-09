<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Test;
class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        

        Test::create([
            'chapter_id' => 2,
            'random' => false,
            'incorrect' => true,
            'score' => true,
            'split' => 1,
            'limited' => 0
        ]);
        Test::create([
            'chapter_id' => 4,
            'random' => false,
            'incorrect' => true,
            'score' => true,
            'split' => 1,
            'limited' => 0
        ]);

        Test::create([
            'chapter_id' => 5,
            'random' => true,
            'incorrect' => false,
            'score' => false,
            'split' => 2,
            'limited' => 2
        ]);

        
        
    }
}
