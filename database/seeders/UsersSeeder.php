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
                'name' => 'Digimentor', // id : 1
                'lastname' => 'Espam Mfl',
                'username' => 'digimentor',
                'email' => 'digimentor.espam@gmail.com',
                'password' => null,
                'registration_method' => 'google',
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
                'name' => 'Luis', // id : 2
                'lastname' => 'Chalacama',
                'username' => 'chalacama',
                'email' => 'luis.chalacama@espam.edu.ec',
                'password' => null,
                'registration_method' => 'google',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Philip', // id : 3
                'lastname' => 'Chalacama',
                'username' => 'philip.chala',
                'email' => 'filyp007lfcm117@gmail.com',
                'password' => null,
                'registration_method' => 'google',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($tutors as $tutorData) {
            $user = User::factory()->create($tutorData);
            $user->assignRole('tutor');
        }

        // Usuarios student
        $students = [
            [
                'name' => 'Yorlli', // id : 4
                'lastname' => 'Chalacama',
                'username' => 'yorlli.chala',
                'email' => 'yoryi.chalacama@gmail.com',
                'password' => null,
                'registration_method' => 'google',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::factory()->create($studentData);
            $user->assignRole('student');
        }
    }
}
