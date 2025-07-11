<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EducationalUser;
class EducationalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EducationalUser::create([
            'sede_id' => 1,
            'user_id' => 1,
            'level' => 1,
            'period' => 'Semestre',
        ]);
        EducationalUser::create([
            'sede_id' => 2,
            'user_id' => 2,
            'level' => 2,
            'period' => 'Bachiller',
        ]);
    }
}
