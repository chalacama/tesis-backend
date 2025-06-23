<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserInformation;
class UserInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserInformation::create([
            'birthdate'        => '2002-01-01',
            'phone_number'     => '0999999997',
            'province'         => 'Guayas',
            'canton'           => 'Guayaquil',
            'parish'           => 'Tarqui',
            'academic_program' => 1, // Ejemplo: Campus Calceta
            'career_id'        => 4, // Ejemplo: Computación
            'semester_id'      => 10,
            'user_id'          => 1,
        ]);
        UserInformation::create([
            'birthdate'        => '2000-01-01',
            'phone_number'     => '099999888',
            'province'         => 'Galápagos',
            'canton'           => 'San Cristóbal',
            'parish'           => 'Puerto Baquerizo Moreno',
            'academic_program' => 2, // Ejemplo: Sede Galápagos
            'career_id'        => 6, // Ejemplo: Ingeniería Ambiental
            'semester_id'      => null,
            'user_id'          => 2,
        ]);
        UserInformation::create([
            'birthdate'        => '2001-01-01',
            'phone_number'     => '099999888',
            'province'         => 'Manabí',
            'canton'           => 'Chone',
            'parish'           => 'San Antonio',
            'academic_program' => 1,
            'career_id'        => 7, // Ejemplo: Medicina Veterinaria
            'semester_id'      => null,
            'user_id'          => 3,
        ]);
        UserInformation::create([
            'birthdate'        => '2003-05-10',
            'phone_number'     => '0988888888',
            'province'         => 'Manabí',
            'canton'           => 'Calceta',
            'parish'           => 'Calceta',
            'academic_program' => 1,
            'career_id'        => 1, // Ejemplo: Administración de Empresas
            'semester_id'      => 1,
            'user_id'          => 4,
        ]);
        UserInformation::create([
            'birthdate'        => '2004-07-15',
            'phone_number'     => '0977777777',
            'province'         => 'Manabí',
            'canton'           => 'Chone',
            'parish'           => 'Chone',
            'academic_program' => 1,
            'career_id'        => 2, // Ejemplo: Administración Pública
            'semester_id'      => 2,
            'user_id'          => 5,
        ]);
    }
}
