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
                'username' => 'chalacama',
                'email' => 'luis.chalacama@espam.edu.ec',
                'password' => bcrypt('password123'),
                'profile_picture_url' => 'https://lh3.googleusercontent.com/a/ACg8ocKPljXTCWcfVZ5TUwdqq2wrFRyNgrVqeQJah7FnbEVE5CVFYno=s389-c-no'
            ],
            [
                'name' => 'Philip', // id : 3
                'lastname' => 'Chalacama',
                'username' => 'philip.chala',
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
                'name' => 'Yorlli', // id : 4
                'lastname' => 'Chalacama',
                'username' => 'yorlli.chala',
                'email' => 'yoryi.chalacama@gmail.com',
                'password' => bcrypt('password123'),
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::factory()->create($studentData);
            $user->assignRole('student');
        }
    }
}
