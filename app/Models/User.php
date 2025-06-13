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

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
    public function tutorCourses()
    {
        return $this->hasMany(TutorCourse::class);
    }

    /**
     * Relación muchos a muchos con Course a través de tutor_courses (cursos donde es tutor).
     */
    public function tutoredCourses()
    {
        return $this->belongsToMany(Course::class, 'tutor_courses')
            ->withPivot('enabled', 'order')
            ->withTimestamps();
    }
    /**
     * Relación uno a muchos con Comment.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Relación uno a muchos con ReplyComment.
     */
    public function replyComments()
    {
        return $this->hasMany(ReplyComment::class);
    }
}
