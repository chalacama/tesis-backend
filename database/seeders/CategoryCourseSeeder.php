<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CategoryCourse;
class CategoryCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Laravel Básico (Programación, Tecnología)
    CategoryCourse::create(['order' => 1, 'course_id' => 1, 'category_id' => 1]); // Programación
    CategoryCourse::create(['order' => 2, 'course_id' => 1, 'category_id' => 5]); // Tecnología

    // 2. Fundamentos de Diseño Gráfico (Diseño)
    CategoryCourse::create(['order' => 1, 'course_id' => 2, 'category_id' => 2]); // Diseño

    // 3. Marketing Digital Estratégico (Marketing)
    CategoryCourse::create(['order' => 1, 'course_id' => 3, 'category_id' => 3]); // Marketing

    // 4. Emprendimiento 101 (Negocios)
    CategoryCourse::create(['order' => 1, 'course_id' => 4, 'category_id' => 4]); // Negocios

    // 5. Introducción a la Inteligencia Artificial (Tecnología, Ciencia de Datos)
    CategoryCourse::create(['order' => 1, 'course_id' => 5, 'category_id' => 5]); // Tecnología
    CategoryCourse::create(['order' => 2, 'course_id' => 5, 'category_id' => 6]); // Ciencia de Datos

    // 6. Análisis de Datos con Python (Ciencia de Datos, Programación)
    CategoryCourse::create(['order' => 1, 'course_id' => 6, 'category_id' => 6]); // Ciencia de Datos
    CategoryCourse::create(['order' => 2, 'course_id' => 6, 'category_id' => 1]); // Programación

    // 7. Finanzas Personales (Finanzas)
    CategoryCourse::create(['order' => 1, 'course_id' => 7, 'category_id' => 7]); // Finanzas

    // 8. Inglés Conversacional (Idiomas)
    CategoryCourse::create(['order' => 1, 'course_id' => 8, 'category_id' => 8]); // Idiomas

    // 9. Fotografía Digital (Fotografía, Arte)
    CategoryCourse::create(['order' => 1, 'course_id' => 9, 'category_id' => 9]); // Fotografía
    CategoryCourse::create(['order' => 2, 'course_id' => 9, 'category_id' => 16]); // Arte

    // 10. Edición de Video Profesional (Video y Animación, Tecnología)
    CategoryCourse::create(['order' => 1, 'course_id' => 10, 'category_id' => 10]); // Video y Animación
    CategoryCourse::create(['order' => 2, 'course_id' => 10, 'category_id' => 5]); // Tecnología

    // 11. Yoga y Mindfulness (Salud y Bienestar)
    CategoryCourse::create(['order' => 1, 'course_id' => 11, 'category_id' => 11]); // Salud y Bienestar

    // 12. Producción Musical (Música, Tecnología)
    CategoryCourse::create(['order' => 1, 'course_id' => 12, 'category_id' => 12]); // Música
    CategoryCourse::create(['order' => 2, 'course_id' => 12, 'category_id' => 5]); // Tecnología

    // 13. Escritura Creativa (Escritura)
    CategoryCourse::create(['order' => 1, 'course_id' => 13, 'category_id' => 13]); // Escritura

    // 14. Pedagogía Moderna (Educación)
    CategoryCourse::create(['order' => 1, 'course_id' => 14, 'category_id' => 14]); // Educación

    // 15. Cocina Internacional (Cocina)
    CategoryCourse::create(['order' => 1, 'course_id' => 15, 'category_id' => 15]); // Cocina

    // 16. Pintura al Óleo (Arte)
    CategoryCourse::create(['order' => 1, 'course_id' => 16, 'category_id' => 16]); // Arte

    // 17. Sostenibilidad Empresarial (Medio Ambiente, Negocios)
    CategoryCourse::create(['order' => 1, 'course_id' => 17, 'category_id' => 17]); // Medio Ambiente
    CategoryCourse::create(['order' => 2, 'course_id' => 17, 'category_id' => 4]); // Negocios

    // 18. Liderazgo Efectivo (Habilidades Personales, Negocios)
    CategoryCourse::create(['order' => 1, 'course_id' => 18, 'category_id' => 18]); // Habilidades Personales
    CategoryCourse::create(['order' => 2, 'course_id' => 18, 'category_id' => 4]); // Negocios

    // 19. Ingeniería de Software (Ingeniería, Programación)
    CategoryCourse::create(['order' => 1, 'course_id' => 19, 'category_id' => 19]); // Ingeniería
    CategoryCourse::create(['order' => 2, 'course_id' => 19, 'category_id' => 1]); // Programación

    // 20. Fundamentos de Biología (Ciencias)
    CategoryCourse::create(['order' => 1, 'course_id' => 20, 'category_id' => 20]); // Ciencias

    // 21. JavaScript Avanzado (Programación, Tecnología)
    CategoryCourse::create(['order' => 1, 'course_id' => 21, 'category_id' => 1]); // Programación
    CategoryCourse::create(['order' => 2, 'course_id' => 21, 'category_id' => 5]); // Tecnología

    // 22. Diseño UX/UI (Diseño, Tecnología)
    CategoryCourse::create(['order' => 1, 'course_id' => 22, 'category_id' => 2]); // Diseño
    CategoryCourse::create(['order' => 2, 'course_id' => 22, 'category_id' => 5]); // Tecnología

    // 23. Publicidad en Redes Sociales (Marketing)
    CategoryCourse::create(['order' => 1, 'course_id' => 23, 'category_id' => 3]); // Marketing

    // 24. Gestión de Startups (Negocios, Finanzas)
    CategoryCourse::create(['order' => 1, 'course_id' => 24, 'category_id' => 4]); // Negocios
    CategoryCourse::create(['order' => 2, 'course_id' => 24, 'category_id' => 7]); // Finanzas

    // 25. Análisis de Big Data (Ciencia de Datos, Tecnología)
    CategoryCourse::create(['order' => 1, 'course_id' => 25, 'category_id' => 6]); // Ciencia de Datos
    CategoryCourse::create(['order' => 2, 'course_id' => 25, 'category_id' => 5]); // Tecnología

    // 26. Fotografía de Retrato (Fotografía)
    CategoryCourse::create(['order' => 1, 'course_id' => 26, 'category_id' => 9]); // Fotografía

    // 27. Producción de Podcasts (Video y Animación, Tecnología)
    CategoryCourse::create(['order' => 1, 'course_id' => 27, 'category_id' => 10]); // Video y Animación
    CategoryCourse::create(['order' => 2, 'course_id' => 27, 'category_id' => 5]); // Tecnología

    // 28. Nutrición Básica (Salud y Bienestar)
    CategoryCourse::create(['order' => 1, 'course_id' => 28, 'category_id' => 11]); // Salud y Bienestar

    // 29. Teoría Musical (Música)
    CategoryCourse::create(['order' => 1, 'course_id' => 29, 'category_id' => 12]); // Música

    // 30. Física Básica (Ciencias)
    CategoryCourse::create(['order' => 1, 'course_id' => 30, 'category_id' => 20]); // Ciencias
    }
}
