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
                'lastname' => 'prime',
                'username' => 'admin.prime',
                'email' => 'admin.prime@example.com',
                'password' => bcrypt('admin123'),
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
                'email' => 'ana@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'name' => 'Philip', // id : 3
                'lastname' => 'Chala',
                'username' => 'chalacama',
                'email' => 'Chala@example.com',
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
                'email' => 'juan@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'name' => 'Oliver', // id : 5
                'lastname' => 'Loor',
                'username' => 'looroliver',
                'email' => 'oliver@example.com',
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
