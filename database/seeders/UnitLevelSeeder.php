<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UnitLevel;
class UnitLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UnitLevel::create([
            'educational_unit_id' => 1,
            'educational_level_id' => 1,
        ]);
        UnitLevel::create([
            'educational_unit_id' => 1,
            'educational_level_id' => 2,
        ]);
        UnitLevel::create([
            'educational_unit_id' => 1,
            'educational_level_id' => 2,
        ]);
    }
}
