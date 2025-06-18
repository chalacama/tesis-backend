<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ModuleAttempt;
use Carbon\Carbon;
class ModuleAttemptsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ModuleAttempt::create([
            'user_id' => 1,
            'module_id' => 1,
            'attempts_count' => 2,
            'last_attempt_at' => Carbon::now(),
            'approved' => true,
        ]);
        ModuleAttempt::create([
            'user_id' => 2,
            'module_id' => 1,
            'attempts_count' => 1,
            'last_attempt_at' => Carbon::now(),
            'approved' => false,
        ]);
    }
}
