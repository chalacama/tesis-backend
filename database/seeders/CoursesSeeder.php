<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Laravel Básico (Programación, Tecnología) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Laravel Básico',
        'description' => 'Curso introductorio a Laravel para desarrollar aplicaciones web modernas.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);
    

    // 2. Fundamentos de Diseño Gráfico (Diseño) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Fundamentos de Diseño Gráfico',
        'description' => 'Aprende los principios básicos del diseño gráfico y herramientas como Adobe Photoshop.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);
    

    // 3. Marketing Digital Estratégico (Marketing) - Active, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Marketing Digital Estratégico',
        'description' => 'Domina estrategias de SEO, SEM y redes sociales para impulsar marcas.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 2,
    ]);
    

    // 4. Emprendimiento 101 (Negocios) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Emprendimiento 101',
        'description' => 'Guía práctica para iniciar y gestionar tu propio negocio.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);
    

    // 5. Introducción a la Inteligencia Artificial (Tecnología, Ciencia de Datos) - Active, Private, Intermediate
    $course = Course::create([
        'title' => 'Introducción a la Inteligencia Artificial',
        'description' => 'Explora los fundamentos de IA y su aplicación en análisis de datos.',
        'private' => true,
        'enabled' => true,
        'difficulty_id' => 2,
    ]);
    

    // 6. Análisis de Datos con Python (Ciencia de Datos, Programación) - Active, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Análisis de Datos con Python',
        'description' => 'Aprende a analizar y visualizar datos usando Python y sus bibliotecas.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 2,
    ]);
    

    // 7. Finanzas Personales (Finanzas) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Finanzas Personales',
        'description' => 'Técnicas para gestionar tu presupuesto e inversiones personales.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);
    

    // 8. Inglés Conversacional (Idiomas) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Inglés Conversacional',
        'description' => 'Mejora tus habilidades lingüísticas en inglés para la vida diaria.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);
    

    // 9. Fotografía Digital (Fotografía, Arte) - Active, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Fotografía Digital',
        'description' => 'Domina técnicas de fotografía y edición para crear imágenes impactantes.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 2,
    ]);
    

    // 10. Edición de Video Profesional (Video y Animación) - Active, Private, Advanced
    $course = Course::create([
        'title' => 'Edición de Video Profesional',
        'description' => 'Aprende a editar videos con herramientas como Adobe Premiere y After Effects.',
        'private' => true,
        'enabled' => true,
        'difficulty_id' => 3,
    ]);
    

    // 11. Yoga y Mindfulness (Salud y Bienestar) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Yoga y Mindfulness',
        'description' => 'Técnicas de yoga y meditación para mejorar tu bienestar mental y físico.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);
    

    // 12. Producción Musical (Música, Tecnología) - Active, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Producción Musical',
        'description' => 'Crea música profesional con herramientas digitales como Ableton Live.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 2,
    ]);
    

    // 13. Escritura Creativa (Escritura) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Escritura Creativa',
        'description' => 'Desarrolla tu capacidad para escribir historias y textos creativos.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);
    

    // 14. Pedagogía Moderna (Educación) - Active, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Pedagogía Moderna',
        'description' => 'Técnicas innovadoras para la enseñanza y el aprendizaje efectivo.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 2,
    ]);
   

    // 15. Cocina Internacional (Cocina) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Cocina Internacional',
        'description' => 'Aprende recetas y técnicas culinarias de todo el mundo.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);
    

    // 16. Pintura al Óleo (Arte) - Active, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Pintura al Óleo',
        'description' => 'Técnicas avanzadas para pintar con óleos y crear obras de arte.',
        'private' => false,
        'enabled' => true ,
        'difficulty_id' => 2,
    ]);
 

    // 17. Sostenibilidad Empresarial (Medio Ambiente, Negocios) - Active, Private, Advanced
    $course = Course::create([
        'title' => 'Sostenibilidad Empresarial',
        'description' => 'Estrategias para integrar prácticas sostenibles en los negocios.',
        'private' => true,
        'enabled' => true,
        'difficulty_id' => 3,
    ]);


    // 18. Liderazgo Efectivo (Habilidades Personales, Negocios) - Active, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Liderazgo Efectivo',
        'description' => 'Desarrolla habilidades de liderazgo y gestión de equipos.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 2,
    ]);
  

    // 19. Ingeniería de Software (Ingeniería, Programación) - Active, Non-Private, Advanced
    $course = Course::create([
        'title' => 'Ingeniería de Software',
        'description' => 'Conceptos avanzados de desarrollo y arquitectura de software.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 3,
    ]);
  

    // 20. Fundamentos de Biología (Ciencias) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Fundamentos de Biología',
        'description' => 'Introducción a los conceptos básicos de biología y ciencias naturales.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);


    // 21. JavaScript Avanzado (Programación, Tecnología) - Active, Private, Advanced
    $course = Course::create([
        'title' => 'JavaScript Avanzado',
        'description' => 'Domina JavaScript para crear aplicaciones web interactivas.',
        'private' => true,
        'enabled' => true,
        'difficulty_id' => 3,
    ]);


    // 22. Diseño UX/UI (Diseño, Tecnología) - Active, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Diseño UX/UI',
        'description' => 'Crea interfaces intuitivas y experiencias de usuario optimizadas.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 2,
    ]);


    // 23. Publicidad en Redes Sociales (Marketing) - Active, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Publicidad en Redes Sociales',
        'description' => 'Aprende a crear campañas efectivas en plataformas como Instagram y Facebook.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 1,
    ]);


    // 24. Gestión de Startups (Negocios, Finanzas) - Active, Private, Advanced
    $course = Course::create([
        'title' => 'Gestión de Startups',
        'description' => 'Estrategias para lanzar y escalar startups exitosas.',
        'private' => true,
        'enabled' => true,
        'difficulty_id' => 3,
    ]);


    // 25. Análisis de Big Data (Ciencia de Datos, Tecnología) - Active, Non-Private, Advanced
    $course = Course::create([
        'title' => 'Análisis de Big Data',
        'description' => 'Técnicas para procesar y analizar grandes volúmenes de datos.',
        'private' => false,
        'enabled' => true,
        'difficulty_id' => 3,
    ]);
 

    // 26. Fotografía de Retrato (Fotografía) - Inactive, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Fotografía de Retrato',
        'description' => 'Técnicas para capturar retratos impactantes y profesionales.',
        'private' => false,
        'enabled' => false,
        'difficulty_id' => 2,
    ]);
  

    // 27. Producción de Podcasts (Video y Animación, Tecnología) - Inactive, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Producción de Podcasts',
        'description' => 'Crea y edita podcasts con herramientas digitales modernas.',
        'private' => false,
        'enabled' => false,
        'difficulty_id' => 1,
    ]);


    // 28. Nutrición Básica (Salud y Bienestar) - Inactive, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Nutrición Básica',
        'description' => 'Principios de nutrición para una vida saludable.',
        'private' => false,
        'enabled' => false,
        'difficulty_id' => 1,
    ]);


    // 29. Teoría Musical (Música) - Inactive, Non-Private, Intermediate
    $course = Course::create([
        'title' => 'Teoría Musical',
        'description' => 'Fundamentos de teoría musical para músicos principiantes y avanzados.',
        'private' => false,
        'enabled' => false,
        'difficulty_id' => 2,
    ]);
 

    // 30. Física Básica (Ciencias) - Inactive, Non-Private, Beginner
    $course = Course::create([
        'title' => 'Física Básica',
        'description' => 'Conceptos fundamentales de física para principiantes.',
        'private' => false,
        'enabled' => false,
        'difficulty_id' => 1,
    ]);

    }
}
