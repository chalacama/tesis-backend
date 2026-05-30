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
        // Roles and Permissions
        $this->call(RolesAndPermissionsSeeder::class);
        //Gestion de usuarios

        $this->call(UsersSeeder::class);
        $this->call(UserInformationSeeder::class);
        //Gestion educativa
        $this->call(EducationalUnitSeeder::class);
        $this->call(CareersSeeder::class);
        $this->call(SedeSeeder::class);
        $this->call(EducationalLevelSeeder::class);
        $this->call(UnitLevelSeeder::class);
        $this->call(CareerSedeSeeder::class);
        $this->call(EducationalUserSeeder::class);
        $this->call(DifficultySeeder::class);
        
        $this->call(CoursesSeeder::class);

        //$this->call(RatingCourseSeeder::class);

        $this->call(TutorCourseSeeder::class);
        
        $this->call(CategoriesSeeder::class);
        
        $this->call(CategoryCourseSeeder::class);
        
        //$this->call(CommentsSeeder::class);
       
        $this->call(ModulesSeeder::class);
        
        $this->call(ChaptersSeeder::class);
        
        $this->call(TypeLearningContentSeeder::class);
        
        $this->call(LearningContentsSeeder::class);
        
        $this->call(TypeQuestionsSeeder::class);
        
        
        
        $this->call(TestSeeder::class);
        $this->call(QuestionsSeeder::class);
        $this->call(AnswersSeeder::class);
        $this->call(TestViewSeeder::class);
        
        
       
        //$this->call(RegistrationsSeeder::class);
       
        //$this->call(CertificatesSeeder::class);
        
        //$this->call(ContentViewsSeeder::class);
        
        //$this->call(UserAnswersSeeder::class);

        //$this->call(CompletedChapterSeeder::class);
        
        //$this->call(LikeChaptersSeeder::class);
        
        //$this->call(SavedCoursesSeeder::class);
        $this->call(TypeThumbnailSeeder::class);
        $this->call(MiniatureCoursesSeeder::class);
        
        $this->call(UserCategoryInterestSeeder::class);

        $this->call(CareerCourseSeeder::class);
        //$this->call(LikeCommentSeeder::class);
        //$this->call(SuggestionSeeder::class);
        // UnitLevelSeeder
    }
}
