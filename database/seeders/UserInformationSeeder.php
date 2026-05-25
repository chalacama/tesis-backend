<?php

namespace Database\Seeders;

use App\Models\UserInformation;
use Illuminate\Database\Seeder;

class UserInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserInformation::create([
            'birthdate' => '1999-04-30',
            'phone_number' => '+593994861344',
            'province_id' => 1,      // ID de GUAYAS en provincias.json
            'canton_id'   => 101,    // ID de GUAYAQUIL
            'parish_id'   => 10101,  // ID de TARQUI
            'user_id' => 1,
            'sexo' => 'masculino',
            'discapacidad'=>'no',
            'estado_civil' => 'soltero/a',
            'discapacidad_permanente' => null,
            'asistencia_establecimiento_discapacidad' => null,
        ]);

        UserInformation::create([
            'birthdate' => '2000-01-01',
            'phone_number' => '+593963856048',
            'cedula' => '1316675295',
            'province_id' => 1,      // ID de GUAYAS en provincias.json
            'canton_id'   => 101,    // ID de GUAYAQUIL
            'parish_id'   => 10101,  // ID de TARQUI
            'user_id' => 2,
            'sexo' => 'masculino',
            'discapacidad'=>'si',
            'estado_civil' => 'soltero/a',
            'discapacidad_permanente' => "visual (ceguera)",
            'asistencia_establecimiento_discapacidad' => "si",
        ]);

    }
}
