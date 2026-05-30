<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserCategoryInterest;
class UserCategoryInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User 1 (Admin) - 4 interests (broad interests as admin)
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 1]); // Programación
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 3]); // Marketing
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 4]); // Negocios
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 5]); // Tecnología

    // User 2 (Tutor: Laravel, IA, Fotografía, Música) - 4 interests
    UserCategoryInterest::create(['user_id' => 2, 'category_id' => 1]); // Programación
    UserCategoryInterest::create(['user_id' => 2, 'category_id' => 5]); // Tecnología
    UserCategoryInterest::create(['user_id' => 2, 'category_id' => 9]); // Fotografía
    UserCategoryInterest::create(['user_id' => 2, 'category_id' => 12]); // Música

    // User 3 (Tutor: Diseño, Idiomas, Fotografía, Música, Educación) - 4 interests
    UserCategoryInterest::create(['user_id' => 3, 'category_id' => 2]); // Diseño
    UserCategoryInterest::create(['user_id' => 3, 'category_id' => 8]); // Idiomas
    UserCategoryInterest::create(['user_id' => 3, 'category_id' => 9]); // Fotografía
    UserCategoryInterest::create(['user_id' => 3, 'category_id' => 16]); // Arte

    // User 4 (Estudiante: Marketing, Ciencia de Datos, Video, IA, Cocina) - 4 interests
    UserCategoryInterest::create(['user_id' => 4, 'category_id' => 3]); // Marketing
    UserCategoryInterest::create(['user_id' => 4, 'category_id' => 5]); // Tecnología
    UserCategoryInterest::create(['user_id' => 4, 'category_id' => 6]); // Ciencia de Datos
    UserCategoryInterest::create(['user_id' => 4, 'category_id' => 10]); // Video y Animación

    
    }
}
