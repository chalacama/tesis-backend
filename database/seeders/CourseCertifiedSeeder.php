<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseCertified;
class CourseCertifiedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CourseCertified::create([
            'course_id' => 1,
            'is_certified' => true,
            'is_unlimited' => false,
            'max_attempts' => 3,
        ]);
        CourseCertified::create([
            'course_id' => 2,
            'is_certified' => false,
            'is_unlimited' => true,
            'max_attempts' => 0,
        ]);
    }
}
