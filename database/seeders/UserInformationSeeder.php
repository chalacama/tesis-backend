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
            'user_id'          => 1,
        ]);
        UserInformation::create([
            'birthdate'        => '2000-01-01',
            'phone_number'     => '099999888',
            'province'         => 'Galápagos',
            'canton'           => 'San Cristóbal',
            'parish'           => 'Puerto Baquerizo Moreno',
            'user_id'          => 2,
        ]);
        UserInformation::create([
            'birthdate'        => '2001-01-01',
            'phone_number'     => '099999888',
            'province'         => 'Manabí',
            'canton'           => 'Chone',
            'parish'           => 'San Antonio',
            'user_id'          => 3,
        ]);
        UserInformation::create([
            'birthdate'        => '2003-05-10',
            'phone_number'     => '0988888888',
            'province'         => 'Manabí',
            'canton'           => 'Calceta',
            'parish'           => 'Calceta',
            'user_id'          => 4,
        ]);
        UserInformation::create([
            'birthdate'        => '2004-07-15',
            'phone_number'     => '0977777777',
            'province'         => 'Manabí',
            'canton'           => 'Chone',
            'parish'           => 'San Antonio',
            'user_id'          => 5,
        ]);
    }
}
