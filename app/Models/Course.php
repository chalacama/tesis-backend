<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\TutorCourse;
use App\Models\User;
use App\Models\RatingCourse;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Registration;
use App\Models\SavedCourse;
use Illuminate\Database\Eloquent\SoftDeletes;
class Course extends Model
{
    use SoftDeletes,HasFactory; 
  
    protected $fillable = [
    'title',
    'description',
    'enabled',
    ];
    /**
     * Relación uno a muchos con TutorCourse.
     */
    public function tutorCourses()
    {
        return $this->hasMany(TutorCourse::class);
    }

    /**
     * Relación muchos a muchos con User a través de tutor_courses (tutores del curso).
     */
    public function tutors()
    {
        return $this->belongsToMany(User::class, 'tutor_courses')
            ->withPivot('enabled')
            ->withTimestamps();
    }

    /**
     * Relación uno a muchos con RatingCourse.
     */
    public function ratingCourses()
    {
        return $this->hasMany(RatingCourse::class);
    }

    /**
     * Relación muchos a muchos con User a través de rating_courses (usuarios que calificaron).
     */
    public function usersRated()
    {
        return $this->belongsToMany(User::class, 'rating_courses')
            ->withPivot('stars')
            ->withTimestamps();
    }
     /**
     * Relación muchos a muchos con Category a través de category_courses.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_courses');
    }
    /**
     * RELACIÓN DE COMENTARIOS ACTUALIZADA
     *
     * Obtiene solo los comentarios PRINCIPALES (sin respuestas) del curso.
     * La relación polimórfica 'commentable' se encarga de la magia.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id');
    }

    /**
     * NUEVA RELACIÓN PARA CONTEO TOTAL
     *
     * Obtiene TODOS los comentarios asociados al curso, incluyendo las respuestas.
     * Ideal para usar con withCount().
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    /**
     * Relación uno a muchos con Module.
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
    /**
     * Relación uno a muchos con Registration.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    /**
     * Relación uno a muchos con SavedCourse.
     */
    public function savedCourses()
    {
        return $this->hasMany(SavedCourse::class);
    }
    /**
     * Relación uno a uno con CourseCertified.
     */
    public function certified()
    {
        return $this->hasOne(CourseCertified::class);
    }
    /**
     * Relación uno a muchos con MiniatureCourse.
     */
    public function miniatures()
    {
        return $this->hasMany(MiniatureCourse::class);
    }
}
