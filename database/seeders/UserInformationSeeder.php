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
            'university'    => 'Espam',
            'career'        => 'Computación',
            'semester'      => '10',
            'birthdate'     => '2002-01-01',
            'phone_number'  => '0999999997',
            'province'      => 'Guayas',
            'canton'        => 'Guayaquil',
            'parish'        => 'Tarqui',
            'user_id'       => 1, // Asegúrate de que el usuario con ID 1 exista
        ]);
        UserInformation::create([
            'university'    => 'Espam',
            'career'        => 'ambiente',
            'semester'      => '8',
            'birthdate'     => '2000-01-01',
            'phone_number'  => '099999888',
            'province'      => 'galápagos',
            'canton'        => 'San Cristóbal',
            'parish'        => 'Puerto Baquerizo Moreno',
            'user_id'       => 2, // Asegúrate de que el usuario con ID 1 exista
        ]);
        UserInformation::create([
            'university'    => 'Espam',
            'career'        => 'veterinaria',
            'semester'      => '3',
            'birthdate'     => '2001-01-01',
            'phone_number'  => '099999888',
            'province'      => 'Manabí',
            'canton'        => 'Chone',
            'parish'        => 'San Antonio',
            'user_id'       => 3, // Asegúrate de que el usuario con ID 1 exista
        ]);
    }
}
