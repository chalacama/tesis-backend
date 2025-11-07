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
        // Schema::create('tests', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('chapter_id');
        //     $table->boolean('random')->default(true);
        //     $table->boolean('incorrect')->default(true);
        //     $table->boolean('score')->default(false);
        //     $table->boolean('split')->default(false);
        //     $table->timestamps();
        // });
        

        Test::create([
            'chapter_id' => 1,
            'random' => true,
            'incorrect' => true,
            'score' => true,
            'split' => 2,
            'limited' => 0
        ]);

        Test::create([
            'chapter_id' => 2,
            'random' => false,
            'incorrect' => true,
            'score' => false,
            'split' => 1,
            'limited' => 0
        ]);

        Test::create([
            'chapter_id' => 4,
            'random' => false,
            'incorrect' => false,
            'score' => true,
            'split' => 1,
            'limited' => 2
        ]);
        
    }
}
