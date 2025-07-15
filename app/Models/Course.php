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
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
class Course extends Model
{
    use SoftDeletes,HasFactory; 
  
    protected $fillable = [
    'title',
    'description',
    'private',
    'code',
    'enabled',
    'difficulty_id',
    ];
     protected $casts = [
        'private' => 'boolean',
        'enabled' => 'boolean',
    ];
    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            // Generar código solo si el curso es privado y no se ha proporcionado uno.
            if ($course->private && is_null($course->code)) {
                $course->code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Genera un código de invitación único y legible para los humanos.
     *
     * @param int $length La longitud del código a generar.
     * @return string El código único generado.
     */
    public static function generateUniqueCode(int $length = 7): string
    {
        // Caracteres permitidos: se excluyen caracteres ambiguos como 0, O, 1, I, L.
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        // Bucle para asegurar que el código generado sea único en la base de datos.
        do {
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                // Usamos random_int para una generación criptográficamente segura.
                $randomString .= $characters[random_int(0, $charactersLength - 1)];
            }
        } while (self::where('code', $randomString)->exists()); // Verifica si el código ya existe.

        return $randomString;
    }

    
/**
     * Relación muchos a muchos con usuarios a través de la tabla pivote tutor_courses.
     * Incluye el campo 'is_owner' de la tabla pivote.
     */
    public function tutors()
    {
        return $this->belongsToMany(User::class, 'tutor_courses')
                    ->withPivot('is_owner') // Carga el campo 'is_owner' de la tabla pivote
                    ->withTimestamps(); // Carga created_at y updated_at de la tabla pivote
    }

    /**
     * Obtiene el dueño del curso.
     * Asume que solo hay un dueño por curso.
     */
    public function owner()
    {
        return $this->tutors()->wherePivot('is_owner', true);
    }

    /**
     * Obtiene los tutores colaboradores del curso.
     */
    public function collaborators()
    {
        return $this->tutors()->wherePivot('is_owner', false);
    }
    /**
     * Relación muchos a muchos con User a través de tutor_courses (tutores del curso).
     */
    

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
    public function careers()
{
    return $this->belongsToMany(Career::class, 'career_courses');
}
public function invitations()
{
    return $this->hasMany(CourseInvitation::class);
}
/**
     * Relación pertenece a Difficulty.
     */
    public function difficulty()
    {
        return $this->belongsTo(Difficulty::class);
    }
}
