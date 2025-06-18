<?php

namespace Database\Seeders;

/* use App\Models\User; */
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        // Llamar al seeder de categorías
        $this->call(CategoriesSeeder::class);
        // Llamar al seeder de category_courses
        $this->call(CategoryCourseSeeder::class);
        // Llamar al seeder de comentarios
        $this->call(CommentsSeeder::class);
        // Llamar al seeder de reply_comments
        $this->call(ReplyCommentsSeeder::class);
        // Llamar al seeder de modules
        $this->call(ModulesSeeder::class);
        // Llamar al seeder de tipos de contenido de aprendizaje
        $this->call(TypeLearningContentSeeder::class);
        // Llamar al seeder de contenidos de aprendizaje
        $this->call(LearningContentsSeeder::class);
        // Llamar al seeder de tipos de formularios
        $this->call(TypeFormsSeeder::class);
        // Llamar al seeder de formularios
        $this->call(FormsSeeder::class);
        // Llamar al seeder de preguntas
        $this->call(TypeQuestionsSeeder::class);
        // Llamar al seeder de preguntas
        $this->call(QuestionsSeeder::class);
        // Llamar al seeder de respuestas
        $this->call(AnswersSeeder::class);
        // Llamar al seeder de respuestas
        $this->call(ChaptersSeeder::class);
        // Llamar al seeder de preguntas de capítulos
        $this->call(ChapterQuestionsSeeder::class);
        // Llamar al seeder de respuestas de capítulos
        $this->call(RegistrationsSeeder::class);
        // Llamar al seeder de certificados de registro
        $this->call(RegistrationCertificatesSeeder::class);
        // Llamar al seeder de contenidos completados
        $this->call(CompletedContentsSeeder::class);
        // Llamar al seeder de vistas de contenido
        $this->call(ContentViewsSeeder::class);
        // Llamar al seeder de respuestas de usuario
        $this->call(UserAnswersSeeder::class);
        // Llamar al seeder de intentos de módulo
        $this->call(ModuleAttemptsSeeder::class);
        // Llamar al seeder de intentos de contenido
        $this->call(LikeLearningContentsSeeder::class);
        // Llamar al seeder de cursos guardados
        $this->call(SavedCoursesSeeder::class);
        // User::factory(10)->create();

        /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */
    }
}
