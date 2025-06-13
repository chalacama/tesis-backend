<?php

namespace Database\Seeders;

/* use App\Models\User; */
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserInformationSeeder;
use Database\Seeders\CoursesSeeder;
use Database\Seeders\RatingCourseSeeder;
use Database\Seeders\TutorCourseSeeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar al seeder de roles y permisos
        $this->call(RolesAndPermissionsSeeder::class);
        // Llamar al seeder de usuarios
        $this->call(UsersSeeder::class);
        // Llamar al seeder de user_information
        $this->call(UserInformationSeeder::class);
        // Llamar al seeder de cursos
        $this->call(CoursesSeeder::class);
        // Llamar al seeder de rating_courses
        $this->call(RatingCourseSeeder::class);
        // Llamar al seeder de tutor_courses
        $this->call(TutorCourseSeeder::class);






        // User::factory(10)->create();

        /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */
    }
}
