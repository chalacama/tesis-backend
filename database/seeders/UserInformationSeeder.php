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
            'birthdate' => '2002-01-01',
            'phone_number' => '0999999997',
            'province' => 'Guayas',
            'canton' => 'Guayaquil',
            'parish' => 'Tarqui',
            'user_id' => 1,
            'sexo' => 'masculino',
            'discapacidad'=>'no',
            'estado_civil' => 'soltero/a',
            'discapacidad_permanente' => null,
            'asistencia_establecimiento_discapacidad' => null,
        ]);

        UserInformation::create([
            'birthdate' => '2000-01-01',
            'phone_number' => '099999888',
            'province' => 'Galápagos',
            'canton' => 'San Cristóbal',
            'parish' => 'Puerto Baquerizo Moreno',
            'user_id' => 2,
            'sexo' => 'femenino',
            'discapacidad'=>'si',
            'estado_civil' => 'soltero/a',
            'discapacidad_permanente' => "visual (ceguera)",
            'asistencia_establecimiento_discapacidad' => "si",
        ]);

        UserInformation::create([
            'birthdate' => '2001-01-01',
            'phone_number' => '099999888',
            'province' => 'Manabí',
            'canton' => 'Chone',
            'parish' => 'San Antonio',
            'user_id' => 3,
            'sexo' => 'masculino',
            'estado_civil' => 'unido/a',
            'discapacidad'=>'si',
            'discapacidad_permanente' => 'visual (ceguera)',
            'asistencia_establecimiento_discapacidad' => 'no',
        ]);

        UserInformation::create([
            'birthdate' => '2003-05-10',
            'phone_number' => '0988888888',
            'province' => 'Manabí',
            'canton' => 'Calceta',
            'parish' => 'Calceta',
            'user_id' => 4,
            'sexo' => 'femenino',
            'estado_civil' => 'soltero/a',
            'discapacidad'=>'no',
            'discapacidad_permanente' => null,
            'asistencia_establecimiento_discapacidad' => null,
        ]);

        UserInformation::create([
            'birthdate' => '2004-07-15',
            'phone_number' => '0977777777',
            'province' => 'Manabí',
            'canton' => 'Chone',
            'parish' => 'San Antonio',
            'user_id' => 5,
            'sexo' => 'masculino',
            'estado_civil' => 'soltero/a',
            'discapacidad'=>'si',
            'discapacidad_permanente' => 'auditiva (sordera)',
            'asistencia_establecimiento_discapacidad' => 'si',
        ]);

    }
}
