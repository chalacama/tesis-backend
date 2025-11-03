<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TestView;
use Carbon\Carbon;
class TestViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /* TestView::create([
            'user_id' => 1,
            'test_id' => 1,
            'completed_at' => Carbon::createFromDate(2025, 11, 2)->toDateTimeString(), // Set the date to January 1, 2022
            
        ]);
        TestView::create([
            'user_id' => 2,
            'test_id' => 1,
            'completed_at' => null,
            
        ]);

        TestView::create([
            'user_id' => 2,
            'test_id' => 2,
            'completed_at' => Carbon::createFromDate(2025, 11, 2)->toDateTimeString(),
            
        ]); */


    }
}
