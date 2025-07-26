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
        'url' => 'https://i.ytimg.com/vi/AE5U8zjkU2s/maxresdefault.jpg', // Coding-related image
    ]);
    MiniatureCourse::create([
        'course_id' => 2, // Fundamentos de Diseño Gráfico
        'url' => 'https://edteam-media.s3.amazonaws.com/courses/original/242a3c4d-0182-4164-afc2-4649ce119c5b.jpg', // Design tools
    ]);
    MiniatureCourse::create([
        'course_id' => 3, // Marketing Digital Estratégico
        'url' => 'https://conextados.com/wp-content/uploads/2025/03/banner-marketing-estrategico.png', // Digital marketing
    ]);
    MiniatureCourse::create([
        'course_id' => 4, // Emprendimiento 101
        'url' => 'https://pbs.twimg.com/media/Fh2XF6fXkAQrUgn.jpg', // Business startup
    ]);
    MiniatureCourse::create([
        'course_id' => 5, // Introducción a la Inteligencia Artificial
        'url' => 'https://fdlformacion.com/wp-content/uploads/2025/04/introduccion-IA.jpg', // AI tech
    ]);
    MiniatureCourse::create([
        'course_id' => 6, // Análisis de Datos con Python
        'url' => 'https://codigofacilito.com/system/courses/thumbnails/000/000/296/original/preview-full-Frame_121.png', // Data visualization
    ]);
    MiniatureCourse::create([
        'course_id' => 7, // Finanzas Personales
        'url' => 'https://i.ytimg.com/vi/dZH9xsnc9YI/maxresdefault.jpg', // Money and finance
    ]);
    MiniatureCourse::create([
        'course_id' => 8, // Inglés Conversacional
        'url' => 'https://www.ensenalia.com/wp-content/uploads/2020/03/clases_conversacion_ingles_online.jpg', // Language learning
    ]);
    MiniatureCourse::create([
        'course_id' => 9, // Fotografía Digital
        'url' => 'https://abacom.edu.ec/wp-content/uploads/2019/10/WhatsApp-Image-2019-10-03-at-5.56.17-PM.jpeg', // Camera and photography
    ]);
    MiniatureCourse::create([
        'course_id' => 10, // Edición de Video Profesional
        'url' => 'https://i.ytimg.com/vi/Hh-8oS8SdnI/maxresdefault.jpg', // Video editing
    ]);
    MiniatureCourse::create([
        'course_id' => 11, // Yoga y Mindfulness
        'url' => 'https://www.otecimpulsa.cl/wp-content/uploads/2023/01/Yoga-y-Mindfulness.png', // Yoga and wellness
    ]);
    MiniatureCourse::create([
        'course_id' => 12, // Producción Musical
        'url' => 'https://i.ytimg.com/vi/qVmClVkN0J8/maxresdefault.jpg', // Music production
    ]);
    MiniatureCourse::create([
        'course_id' => 13, // Escritura Creativa
        'url' => 'https://escuelaelbs.com/wp-content/uploads/curso-escritura.jpg', // Writing and notebook
    ]);
    MiniatureCourse::create([
        'course_id' => 14, // Pedagogía Moderna
        'url' => 'https://1.bp.blogspot.com/-KEtBF9_Xe6k/VsU22LjvpiI/AAAAAAABCxc/FVbgQW0HdvM/s1600/blogruthdibujos.png', // Education and teaching
    ]);
    MiniatureCourse::create([
        'course_id' => 15, // Cocina Internacional
        'url' => 'https://ccsud.edu.ec/wp-content/uploads/2022/04/cocina-nacional-inter.jpg', // Cooking and food
    ]);
    MiniatureCourse::create([
        'course_id' => 16, // Pintura al Óleo
        'url' => 'https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?q=80&w=800&auto=format&fit=crop', // Painting and art
    ]);
    MiniatureCourse::create([
        'course_id' => 17, // Sostenibilidad Empresarial
        'url' => 'https://blog.bhybrid.com/wp-content/uploads/2021/09/BLOG_SOSTENIBILIDAD_SEPT_PORTADA.png', // Sustainability
    ]);
    MiniatureCourse::create([
        'course_id' => 18, // Liderazgo Efectivo
        'url' => 'https://cdn.adrformacion.com/teleusers/vid_presentacion_cursos/courseImage_LIDER2_1651651991_.jpg', // Leadership
    ]);
    MiniatureCourse::create([
        'course_id' => 19, // Ingeniería de Software
        'url' => 'https://i.ytimg.com/vi/g8Itpc2ww2Q/maxresdefault.jpg', // Software engineering
    ]);
    MiniatureCourse::create([
        'course_id' => 20, // Fundamentos de Biología
        'url' => 'https://0.academia-photos.com/attachment_thumbnails/56639375/mini_magick20190111-16815-1e1y9de.png', // Biology
    ]);
    // MiniatureCourse::create([
    //     'course_id' => 21, // JavaScript Avanzado
    //     'url' => 'https://images.unsplash.com/photo-1516321310762-479e750e7e2b?q=80&w=800&auto=format&fit=crop', // Coding
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 22, // Diseño UX/UI
    //     'url' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=800&auto=format&fit=crop', // UX/UI design
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 23, // Publicidad en Redes Sociales
    //     'url' => 'https://images.unsplash.com/photo-1551288049-b1f3a78a4c4b?q=80&w=800&auto=format&fit=crop', // Social media marketing
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 24, // Gestión de Startups
    //     'url' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?q=80&w=800&auto=format&fit=crop', // Startups
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 25, // Análisis de Big Data
    //     'url' => 'https://images.unsplash.com/photo-1551288049-b1f3a78a4c4b?q=80&w=800&auto=format&fit=crop', // Big data
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 26, // Fotografía de Retrato
    //     'url' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=800&auto=format&fit=crop', // Portrait photography
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 27, // Producción de Podcasts
    //     'url' => 'https://images.unsplash.com/photo-1511671786161-2e6b8b6e6a6e?q=80&w=800&auto=format&fit=crop', // Podcast production
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 28, // Nutrición Básica
    //     'url' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=800&auto=format&fit=crop', // Nutrition
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 29, // Teoría Musical
    //     'url' => 'https://images.unsplash.com/photo-1511671786161-2e6b8b6e6a6e?q=80&w=800&auto=format&fit=crop', // Music theory
    // ]);
    // MiniatureCourse::create([
    //     'course_id' => 30, // Física Básica
    //     'url' => 'https://images.unsplash.com/photo-1507668077129-5e793b1b7d00?q=80&w=800&auto=format&fit=crop', // Physics
    // ]);
        
    }
}
