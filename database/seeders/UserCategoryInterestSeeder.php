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
        // User 1 (Admin) - 5 interests (broad interests as admin)
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 1]); // Programación
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 3]); // Marketing
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 4]); // Negocios
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 5]); // Tecnología
    UserCategoryInterest::create(['user_id' => 1, 'category_id' => 18]); // Habilidades Personales

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

    // User 4 (Tutor: Marketing, Ciencia de Datos, Video, IA, Cocina) - 4 interests
    UserCategoryInterest::create(['user_id' => 4, 'category_id' => 3]); // Marketing
    UserCategoryInterest::create(['user_id' => 4, 'category_id' => 5]); // Tecnología
    UserCategoryInterest::create(['user_id' => 4, 'category_id' => 6]); // Ciencia de Datos
    UserCategoryInterest::create(['user_id' => 4, 'category_id' => 10]); // Video y Animación

    // User 5 (Tutor: Negocios, Finanzas, Salud, Educación, Cocina, Marketing) - 4 interests
    UserCategoryInterest::create(['user_id' => 5, 'category_id' => 4]); // Negocios
    UserCategoryInterest::create(['user_id' => 5, 'category_id' => 7]); // Finanzas
    UserCategoryInterest::create(['user_id' => 5, 'category_id' => 11]); // Salud y Bienestar
    UserCategoryInterest::create(['user_id' => 5, 'category_id' => 15]); // Cocina

    // User 6 - 4 interests
    UserCategoryInterest::create(['user_id' => 6, 'category_id' => 1]); // Programación
    UserCategoryInterest::create(['user_id' => 6, 'category_id' => 2]); // Diseño
    UserCategoryInterest::create(['user_id' => 6, 'category_id' => 3]); // Marketing
    UserCategoryInterest::create(['user_id' => 6, 'category_id' => 8]); // Idiomas

    // User 7 - 3 interests
    UserCategoryInterest::create(['user_id' => 7, 'category_id' => 5]); // Tecnología
    UserCategoryInterest::create(['user_id' => 7, 'category_id' => 18]); // Habilidades Personales
    UserCategoryInterest::create(['user_id' => 7, 'category_id' => 19]); // Ingeniería

    // User 8 - 4 interests
    UserCategoryInterest::create(['user_id' => 8, 'category_id' => 6]); // Ciencia de Datos
    UserCategoryInterest::create(['user_id' => 8, 'category_id' => 9]); // Fotografía
    UserCategoryInterest::create(['user_id' => 8, 'category_id' => 13]); // Escritura
    UserCategoryInterest::create(['user_id' => 8, 'category_id' => 20]); // Ciencias
    }
}
