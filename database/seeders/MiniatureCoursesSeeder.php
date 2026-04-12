<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MiniatureCourse;
class MiniatureCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MiniatureCourse::create([
        'course_id' => 1, // Laravel Básico
        'url' => 'https://storage.googleapis.com/mi-app-storage/courses/1/thumbnail/laravel-basico.jpg', // Coding-related image
        'name' => 'Miniatura del curso Laravel Básico',
        'size_bytes' => 204800, // Ejemplo de tamaño en bytes
        'width' => 1280, // Ancho en píxeles
        'height' => 720, // Alto en píxeles
        'aspect_ratio' => '16:9', // Relación de aspecto
        'type_thumbnail_id' => 2, // "archive.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 2, // Fundamentos de Diseño Gráfico
        'url' => 'https://edteam-media.s3.amazonaws.com/courses/original/242a3c4d-0182-4164-afc2-4649ce119c5b.jpg', // Design tools
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 3, // Marketing Digital Estratégico
        'url' => 'https://conextados.com/wp-content/uploads/2025/03/banner-marketing-estrategico.png', // Digital marketing
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 4, // Emprendimiento 101
        'url' => 'https://pbs.twimg.com/media/Fh2XF6fXkAQrUgn.jpg', // Business startup
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 5, // Introducción a la Inteligencia Artificial
        'url' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1764480685/miniatures/curso/5.jpg', // AI tech
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 6, // Análisis de Datos con Python
        'url' => 'https://codigofacilito.com/system/courses/thumbnails/000/000/296/original/preview-full-Frame_121.png', // Data visualization
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 7, // Finanzas Personales
        'url' => 'https://i.ytimg.com/vi/dZH9xsnc9YI/maxresdefault.jpg', // Money and finance
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 8, // Inglés Conversacional
        'url' => 'https://www.ensenalia.com/wp-content/uploads/2020/03/clases_conversacion_ingles_online.jpg', // Language learning
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 9, // Fotografía Digital
        'url' => 'https://abacom.edu.ec/wp-content/uploads/2019/10/WhatsApp-Image-2019-10-03-at-5.56.17-PM.jpeg', // Camera and photography
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 10, // Edición de Video Profesional
        'url' => 'https://i.ytimg.com/vi/Hh-8oS8SdnI/maxresdefault.jpg', // Video editing
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 11, // Yoga y Mindfulness
        'url' => 'https://www.otecimpulsa.cl/wp-content/uploads/2023/01/Yoga-y-Mindfulness.png', // Yoga and wellness
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 12, // Producción Musical
        'url' => 'https://i.ytimg.com/vi/qVmClVkN0J8/maxresdefault.jpg', // Music production
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 13, // Escritura Creativa
        'url' => 'https://escuelaelbs.com/wp-content/uploads/curso-escritura.jpg', // Writing and notebook
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 14, // Pedagogía Moderna
        'url' => 'https://1.bp.blogspot.com/-KEtBF9_Xe6k/VsU22LjvpiI/AAAAAAABCxc/FVbgQW0HdvM/s1600/blogruthdibujos.png', // Education and teaching
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 15, // Cocina Internacional
        'url' => 'https://ccsud.edu.ec/wp-content/uploads/2022/04/cocina-nacional-inter.jpg', // Cooking and food
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 16, // Pintura al Óleo
        'url' => 'https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?q=80&w=800&auto=format&fit=crop', // Painting and art
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 17, // Sostenibilidad Empresarial
        'url' => 'https://blog.bhybrid.com/wp-content/uploads/2021/09/BLOG_SOSTENIBILIDAD_SEPT_PORTADA.png', // Sustainability
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 18, // Liderazgo Efectivo
        'url' => 'https://cdn.adrformacion.com/teleusers/vid_presentacion_cursos/courseImage_LIDER2_1651651991_.jpg', // Leadership
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 19, // Ingeniería de Software
        'url' => 'https://i.ytimg.com/vi/g8Itpc2ww2Q/maxresdefault.jpg', // Software engineering
        'type_thumbnail_id' => 2, // "link.image"
    ]);
    MiniatureCourse::create([
        'course_id' => 20, // Fundamentos de Biología
        'url' => 'https://0.academia-photos.com/attachment_thumbnails/56639375/mini_magick20190111-16815-1e1y9de.png', // Biology
        'type_thumbnail_id' => 2, // "link.image"
    ]);
        
    }
}
