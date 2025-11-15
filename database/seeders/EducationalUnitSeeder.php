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
            'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logo.png',
        ]);

        EducationalUnit::create([
            'name' => 'Colegio Raymundo Aveiga',
            // 'organization_domain' => 'uce.edu.ec',
            'url_logo' => 'https://www.designevo.com/res/templates/thumb_small/branch-encircled-book-and-torch-shield.webp',
        ]);
    }
}
