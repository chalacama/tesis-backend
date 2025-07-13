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
                'name' => 'admin', // id : 1
                'lastname' => 'espam',
                'username' => 'adminespam',
                'email' => 'admin.espam@espam.edu.ec',
                'password' => bcrypt('S3cur3P@ssw0rd!2025'),
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
                'name' => 'Ana', // id : 2
                'lastname' => 'García',
                'username' => 'anag',
                'email' => 'luis.chalacama@espam.edu.ec',
                'password' => bcrypt('password123'),
            ],
            [
                'name' => 'Philip', // id : 3
                'lastname' => 'Chala',
                'username' => 'chalacama',
                'email' => 'chala@espam.edu.ec',
                'password' => bcrypt('password123'),
            ],
            // Puedes agregar más tutores aquí
        ];

        foreach ($tutors as $tutorData) {
            $user = User::factory()->create($tutorData);
            $user->assignRole('tutor');
        }

        // Usuarios student
        $students = [
            [
                'name' => 'Juan', // id : 4
                'lastname' => 'Pérez',
                'username' => 'juanp',
                'email' => 'juan@gmail.com',
                'password' => bcrypt('password123'),
            ],
            [
                'name' => 'Oliver', // id : 5
                'lastname' => 'Loor',
                'username' => 'looroliver',
                'email' => 'oliver@gmail.com',
                'password' => bcrypt('password123'),
            ],
            // Puedes agregar más estudiantes aquí
        ];

        foreach ($students as $studentData) {
            $user = User::factory()->create($studentData);
            $user->assignRole('student');
        }
    }
}
