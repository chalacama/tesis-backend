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
                'lastname' => 'digi',
                'username' => 'admindigi',
                'email' => 'admin.espam@espam.edu.ec',
                'password' => bcrypt('S3cur3P@ssw0rd!2025'),
                'profile_picture_url' => 'https://i.pinimg.com/736x/70/aa/d2/70aad2738a5e301652843930582fffaa.jpg'
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
                'lastname' => '',
                'username' => 'bautista69',
                'email' => 'luis.chalacama@espam.edu.ec',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://i.pinimg.com/474x/e8/7c/32/e87c325d2487e441901df6330f2b7ad6.jpg'
            ],
            [
                'name' => 'Philip', // id : 3
                'lastname' => 'Chala',
                'username' => 'chalacama',
                'email' => 'chala@espam.edu.ec',
                'password' => bcrypt('password123'),
            ],
            [
                'name' => 'Jandry', // id : 4
                'lastname' => 'Torres',
                'username' => 'minino7',
                'email' => 'jandryxd@espam.edu.ec',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://i.pinimg.com/474x/f1/03/e3/f103e316dcd2cab8e3e8481a1f9a21f2.jpg'
            ],
            [
                'name' => 'Jesus', // id : 5
                'lastname' => '8poll',
                'username' => 'jisus69',
                'email' => 'jesus.39@gmail.com',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://i.pinimg.com/736x/3d/b7/ae/3db7ae3f2cecd4706c85376d49f6879f.jpg'
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
                'name' => 'Juan', // id : 6
                'lastname' => 'Loor',
                'username' => 'juanp',
                'email' => 'juan@esapam.edu.ec',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://i.pinimg.com/736x/83/30/7d/83307da0f2648ecb70a70d585f1f3486.jpg'
            ],
            [
                'name' => 'Oliver', // id : 7
                'lastname' => 'Solorzano',
                'username' => 'oligay',
                'email' => 'oliver@gmail.com',
                'password' => bcrypt('password123'),
            ],
            [
                'name' => 'Aura', // id : 8
                'lastname' => 'Zambrano',
                'username' => 'laurita',
                'email' => 'aurita@gmail.com',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://i.pinimg.com/564x/47/28/10/472810004e29e6433cc1c3498692ab41.jpg'
            ],
            
            // Puedes agregar más estudiantes aquí
        ];

        foreach ($students as $studentData) {
            $user = User::factory()->create($studentData);
            $user->assignRole('student');
        }
    }
}
