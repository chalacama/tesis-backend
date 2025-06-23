<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserCategoryInterest;
class UserCategoryInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserCategoryInterest::create([
            'user_id' => 1,
            'category_id' => 1,
        ]);
        UserCategoryInterest::create([
            'user_id' => 2,
            'category_id' => 2,
        ]);
        UserCategoryInterest::create([
            'user_id' => 3,
            'category_id' => 2,
        ]);
        UserCategoryInterest::create([
            'user_id' => 4,
            'category_id' => 1,
        ]);
    }
}
