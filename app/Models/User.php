<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;



use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\UserInformation;
use App\Models\RatingCourse;
use App\Models\Course;
use App\Models\TutorCourse;
use App\Models\Comment;
use App\Models\Registration;
use App\Models\CompletedContent;
use App\Models\ContentView;
use App\Models\UserAnswer;
use App\Models\ModuleAttempt;
use App\Models\LikeLearningContent;
use App\Models\SavedCourse;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserCategoryInterest;
use App\Models\LikeComment;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use SoftDeletes , HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'google_id',      // Añadido
        'name',
        'lastname',       // Añadido
        'username',       // Añadido
        'email',
        'password',
        'registration_method', // Añadido
        'profile_picture_url',
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * Relación uno a uno con UserInformation.
     */
    public function userInformation()
    {
        return $this->hasOne(UserInformation::class);
    }
    /**
     * Relación uno a uno con EducationalUser.
     */
    public function educationalUser()
    {
        return $this->hasOne(EducationalUser::class);
    }
    /**
     * Relación uno a muchos con RatingCourse.
     */
    public function ratingCourses()
    {
        return $this->hasMany(RatingCourse::class);
    }

    /**
     * Relación muchos a muchos con Course a través de rating_courses.
     */
    public function ratedCourses()
    {
        return $this->belongsToMany(Course::class, 'rating_courses')
            ->withPivot('stars')
            ->withTimestamps();
    }
    /**
     * Relación uno a muchos con TutorCourse.
     */
    /* public function tutorCourses()
    {
        return $this->hasMany(TutorCourse::class);
    } */

    /**
     * Relación muchos a muchos con Course a través de tutor_courses (cursos donde es tutor).
     */
    /* public function tutoredCourses()
    {
        return $this->belongsToMany(Course::class, 'tutor_courses')
            ->withPivot('enabled', 'order')
            ->withTimestamps();
    } */
    /**
     * Relación muchos a muchos con cursos a través de la tabla pivote tutor_courses.
     * Incluye el campo 'is_owner' de la tabla pivote.
     */
    public function tutoredCourses()
    {
        return $this->belongsToMany(Course::class, 'tutor_courses')
                    ->withPivot('is_owner') // Carga el campo 'is_owner' de la tabla pivote
                    ->withTimestamps(); // Carga created_at y updated_at de la tabla pivote
    }

    /**
     * Obtiene los cursos donde el usuario es el dueño.
     */
    public function ownedCourses()
    {
        return $this->coursesAsTutor()->wherePivot('is_owner', true);
    }

    /**
     * Obtiene los cursos donde el usuario es un colaborador.
     */
    public function collaboratedCourses()
    {
        return $this->coursesAsTutor()->wherePivot('is_owner', false);
    }
    /**
     * Relación uno a muchos con Comment.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    
    /**
     * Relación uno a muchos con Registration.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    /**
     * Relación uno a muchos con CompletedContent.
     */
    public function completedContents()
    {
        return $this->hasMany(CompletedContent::class);
    }
    /**
     * Relación uno a muchos con ContentView.
     */
    public function contentViews()
    {
        return $this->hasMany(ContentView::class);
    }
    /**
     * Relación uno a muchos con UserAnswer.
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
    /**
     * Relación uno a muchos con ModuleAttempt.
     */
    public function moduleAttempts()
    {
        return $this->hasMany(ModuleAttempt::class);
    }
    /**
     * Relación uno a muchos con LikeLearningContent.
     */
    public function likeLearningContents()
    {
        return $this->hasMany(LikeLearningContent::class);
    }

    /**
     * Relación uno a muchos con SavedCourse.
     */
    public function savedCourses()
    {
        return $this->hasMany(SavedCourse::class);
    }

    /**
     * Relación uno a muchos con UserCategoryInterest.
     */
    public function categoryInterests()
    {
        return $this->hasMany(UserCategoryInterest::class);
    }
    /**
     * Relación uno a muchos con LikeComment.
     */
    public function likeComments(): HasMany
    {
        return $this->hasMany(LikeComment::class);
    }
}
