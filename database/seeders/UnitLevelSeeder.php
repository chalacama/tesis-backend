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
        // espam.edu.ec - Educación Superior
        UnitLevel::create([
            'educational_unit_id' => 1,
            'educational_level_id' => 1,
        ]);
        // espam.edu.ec - Otros
        UnitLevel::create([
            'educational_unit_id' => 1,
            'educational_level_id' => 4,
        ]);
        // Otras instituciones - Educativas
        UnitLevel::create([
            'educational_unit_id' => 2,
            'educational_level_id' => 1,
        ]);
        UnitLevel::create([
            'educational_unit_id' => 2,
            'educational_level_id' => 2,
        ]);

        UnitLevel::create([
            'educational_unit_id' => 2,
            'educational_level_id' => 3,
        ]);
        UnitLevel::create([
            'educational_unit_id' => 2,
            'educational_level_id' => 4,
        ]);

        // no posee unidad educativa ni nivel educativo
        UnitLevel::create([
            'educational_unit_id' => 3,
            'educational_level_id' => 5,
        ]);
        
    }
}
