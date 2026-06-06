<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuarios admin
        $admins = [
            [
                //'google_id' => '100000000000000000002',
                'name' => 'Digimentor', // id : 1
                'lastname' => 'Espam Mfl',
                'username' => 'digimentor',
                'username_at' => now(),
                'email' => 'digimentor.espam@gmail.com',
                'password' => '123.Password',
                'email_verified_at' => now(),
            ],
            // Puedes agregar más admins aquí
        ];

        foreach ($admins as $adminData) {
            $user = User::factory()->create($adminData);
            $user->assignRole('admin');
        }

        // Usuarios tutor
        $tutors = [
            [
                'name' => 'LUIS FELIPE', // id : 2
                'lastname' => 'CHALACAMA MENDOZA',
                'username' => 'chalacama',
                'cedula' => '1316675295',
                'phone_number' => '+593997047184',
                'email' => 'luis.chalacama@espam.edu.ec',
                'password' => '123.Password',
                'email_verified_at' => now(),
                'username_at' => now(),
                //'phone_verified_at' => now(), // Corregido
                'cedula_verified_at' => now(), // Corregido
            ],
            [
                'name' => 'Philip', // id : 3
                'lastname' => 'Chalacama',
                'username' => 'philip.chala',
                'email' => 'filyp007lfcm117@gmail.com',
                'password' => '123.Password',
                'email_verified_at' => now(),
                'username_at' => now(),
            ],
        ];

        foreach ($tutors as $tutorData) {
            $user = User::factory()->create($tutorData);
            $user->assignRole('tutor');
        }

        // Usuarios student
        $students = [
            [
                //'google_id' => '100000000000000000002',
                'name' => 'Yorlli', // id : 4
                'lastname' => 'Chalacama',
                'username' => 'yorlli.chala',
                'phone_number' => '+593983088926',
                'cedula' => '1306226547',
                'email' => 'yoryi.chalacama@gmail.com',
                'password' => '123.Password',
                'email_verified_at' => now(),
                'username_at' => now(),
                'phone_verified_at' => now(), // Corregido
                'cedula_verified_at' => now(), // Corregido
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::factory()->create($studentData);
            $user->assignRole('student');
        }
    }
}
