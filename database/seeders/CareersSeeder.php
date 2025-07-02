<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Career;
class CareersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $careers = [
    // id: 1
    ['name' => 'Administración de Empresas', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/empresas.png'],
    // id: 2
    ['name' => 'Administración Pública', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/publica.png'],
    // id: 3
    ['name' => 'Agroindustria', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/agroindustria.png'],
    // id: 4
    ['name' => 'Agropecuaria', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/agropecuaria-logo.png'],
    // id: 5
    ['name' => 'Computación', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/computacion.png'],
    // id: 6
    ['name' => 'Electrónica y Automatización', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/electronica.png'],
    // id: 7
    ['name' => 'Gastronomía', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/gastronomia.png'],
    // id: 8
    ['name' => 'Gestión de la Innovación Organizacional y Productividad', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/innovacion.png'],
    // id: 9
    ['name' => 'Ingeniería Agricola', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/agricola.png'],
    // id: 10
    ['name' => 'Ingeniería Agroforestal', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/agroforestal.png'],
    // id: 11
    ['name' => 'Ingeniería Ambiental', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/ambiente.png'],
    // id: 12
    ['name' => 'Ingeniería de la Producción', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/produccion.png'],
    // id: 13
    ['name' => 'Ingeniería en Biotecnología', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/biotecnologia.png'],
    // id: 14
    ['name' => 'Ingeniería en Riesgos de Desastres', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/riesgos.png'],
    // id: 15
    ['name' => 'Marketing Digital', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/marketing.png'],
    // id: 16
    ['name' => 'Medicina Veterinaria', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/veterinaria.png'],
    // id: 17
    ['name' => 'Turismo', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/turismo.png'],
    // id: 18
    ['name' => 'Turismo y Hotelería', 'url_logo' => 'https://www.espam.edu.ec/recursos/plantilla/img/logos/turismohoteleria.png'],
];

        foreach ($careers as $career) {
            Career::create($career);
        }
    }
}
