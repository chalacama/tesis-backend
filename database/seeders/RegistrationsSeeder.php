<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Registration;
class RegistrationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Registration::create([
            'approved' => true,
            'annulment' => false,
            'user_id' => 1,
            'course_id' => 1,
        ]);
        Registration::create([
            'approved' => false,
            'annulment' => true,
            'user_id' => 2,
            'course_id' => 1,
        ]);
    }
}
