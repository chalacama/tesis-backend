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
        // Admin
        UserInformation::create([
            'birthdate' => '1990-01-01',
            'phone_number' => '+593990000001',
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

        // Tutor 1
        UserInformation::create([
            'birthdate' => '2001-11-25',
            'phone_number' => '+593987047148',
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

        // Tutor 2
        UserInformation::create([
            'birthdate' => '2001-11-25',
            'phone_number' => '+593987047148',
            'province_id' => 1,      // ID de GUAYAS en provincias.json
            'canton_id'   => 101,    // ID de GUAYAQUIL
            'parish_id'   => 10101,  // ID de TARQUI
            'user_id' => 3,
            'sexo' => 'masculino',
            'discapacidad'=>'no',
            'estado_civil' => 'casado/a',
            'discapacidad_permanente' => null,
            'asistencia_establecimiento_discapacidad' => null,
        ]);

        // Student
        UserInformation::create([
            'birthdate' => '1967-04-30',
            'phone_number' => '+593983088926',
            'province_id' => 1,      // ID de GUAYAS en provincias.json
            'canton_id'   => 101,    // ID de GUAYAQUIL
            'parish_id'   => 10101,  // ID de TARQUI
            'user_id' => 4,
            'sexo' => 'masculino',
            'discapacidad'=>'no',
            'estado_civil' => 'soltero/a',
            'discapacidad_permanente' => null,
            'asistencia_establecimiento_discapacidad' => null,
        ]);

    }
}
