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
                'name' => 'Admin', // id : 1
                'lastname' => 'Digimentor',
                'username' => 'admindigi',
                'email' => 'digimentor.espam@gmail.com',
                'password' => bcrypt('password123'),
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
                'lastname' => 'Chalacama',
                'username' => 'luis.chalacama',
                'email' => 'luis.chalacama@espam.edu.ec',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://i.pinimg.com/474x/e8/7c/32/e87c325d2487e441901df6330f2b7ad6.jpg'
            ],
            [
                'name' => 'Filyp', // id : 3
                'lastname' => 'Lopez',
                'username' => 'filyp007',
                'email' => 'filyp007lfcm117@gmail.com',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://i.pinimg.com/736x/3d/b7/ae/3db7ae3f2cecd4706c85376d49f6879f.jpg'
            ],
        ];

        foreach ($tutors as $tutorData) {
            $user = User::factory()->create($tutorData);
            $user->assignRole('tutor');
        }

        // Usuarios student
        $students = [
            [
                'name' => 'Yoryi', // id : 4
                'lastname' => 'Chalacama',
                'username' => 'yoryi.chalacama',
                'email' => 'yoryi.chalacama@gmail.com',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://i.pinimg.com/736x/83/30/7d/83307da0f2648ecb70a70d585f1f3486.jpg'
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::factory()->create($studentData);
            $user->assignRole('student');
        }
    }
}
