<?php

namespace Database\Seeders;

use App\Models\EducationalUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EducationalLevel;
class EducationalUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EducationalUnit::create([
            'name' => 'ESPAM MFL',
            'organization_domain' => 'espam.edu.ec',
            'url_logo' => 'https://www.ces.gob.ec/LOGOS_IES/1003.png',
        ]);

        EducationalUnit::create([
            'name' => 'Universidad Central del Ecuador',
            // 'organization_domain' => 'uce.edu.ec',
            'url_logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4Xf3xKSvFqJttgbQAADuoDTdsJFoHhfGgTw&s',
        ]);
    }
}
