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
        // 1 espam.edu.ec
        EducationalUnit::create([
            'name' => 'ESPAM MFL',
            'organization_domain' => 'espam.edu.ec',
            'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logo.png',
        ]);
        // 2 Otras instituciones - Educativas
        EducationalUnit::create([
            'name' => 'Otras sedes educativas',
            'url_logo' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1764369743/logo-otras-instituciones-educativas_thulyo.png',
        ]);
        // 3 no pose de unidad educativa
        EducationalUnit::create([
            'name' => 'Ninguno (No posee ninguna sede educativa)', 
            'url_logo' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1764369806/logo-no-pose-institucion-educativa_kg1myq.png',
        ]);
        //  4 Colegio Raymundo Aveiga
        EducationalUnit::create([
            'name' => 'Colegio Raymundo Aveiga',
            // 'organization_domain' => 'uce.edu.ec',
            'url_logo' => 'https://www.designevo.com/res/templates/thumb_small/branch-encircled-book-and-torch-shield.webp',
        ]);
    }
}
