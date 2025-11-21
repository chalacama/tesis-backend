<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chapter;
class ChaptersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Course 1: Laravel Básico (Module IDs 1, 2, 3) - 3 modules, 7 chapters
    // Module 1: Introducción a Laravel
    Chapter::create([
        'title' => '¿Qué es Laravel?',
        'description' => 'Video introductorio sobre el framework Laravel.',
        'module_id' => 1,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Instalación y Configuración',
        'description' => 'Guía práctica para instalar Laravel.',
        'module_id' => 1,
        'order' => 2,
    ]);
    // Module 2: Rutas y Controladores
    Chapter::create([
        'title' => 'Definición de Rutas',
        'description' => 'Cómo crear rutas en Laravel.',
        'module_id' => 2,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Controladores Básicos',
        'description' => 'Creación de controladores para manejar lógica.',
        'module_id' => 2,
        'order' => 2,
    ]);
    // Module 3: Eloquent ORM
    Chapter::create([
        'title' => 'Modelos y Relaciones',
        'description' => 'Uso de Eloquent para gestionar datos.',
        'module_id' => 3,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Consultas Avanzadas',
        'description' => 'Técnicas avanzadas con Eloquent ORM.',
        'module_id' => 3,
        'order' => 2,
    ]);
    Chapter::create([
        'title' => 'Migraciones',
        'description' => 'Creación de esquemas de base de datos.',
        'module_id' => 3,
        'order' => 3,
    ]);

    // Course 2: Fundamentos de Diseño Gráfico (Module IDs 4, 5, 6) - 3 modules, 6 chapters
    // Module 4: Principios del Diseño
    Chapter::create([
        'title' => 'Teoría del Color',
        'description' => 'Introducción al uso del color en diseño.',
        'module_id' => 4,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Tipografía',
        'description' => 'Conceptos básicos de tipografía en diseño.',
        'module_id' => 4,
        'order' => 2,
    ]);
    // Module 5: Herramientas de Diseño
    Chapter::create([
        'title' => 'Adobe Photoshop Básico',
        'description' => 'Introducción a Photoshop para diseño gráfico.',
        'module_id' => 5,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Adobe Illustrator',
        'description' => 'Uso de Illustrator para crear gráficos vectoriales.',
        'module_id' => 5,
        'order' => 2,
    ]);
    // Module 6: Creación de Composiciones
    Chapter::create([
        'title' => 'Diseño de Pósters',
        'description' => 'Cómo crear pósters impactantes.',
        'module_id' => 6,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Composición Visual',
        'description' => 'Técnicas para composiciones equilibradas.',
        'module_id' => 6,
        'order' => 2,
    ]);

    // Course 3: Marketing Digital Estratégico (Module IDs 7, 8, 9) - 3 modules, 6 chapters
    // Module 7: Fundamentos de SEO
    Chapter::create([
        'title' => 'Introducción al SEO',
        'description' => 'Conceptos básicos de optimización para buscadores.',
        'module_id' => 7,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Palabras Clave',
        'description' => 'Cómo investigar palabras clave efectivas.',
        'module_id' => 7,
        'order' => 2,
    ]);
    // Module 8: Publicidad en Redes Sociales
    Chapter::create([
        'title' => 'Campañas en Facebook',
        'description' => 'Creación de anuncios en Facebook Ads.',
        'module_id' => 8,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Instagram Ads',
        'description' => 'Estrategias para publicidad en Instagram.',
        'module_id' => 8,
        'order' => 2,
    ]);
    // Module 9: Análisis de Campañas
    Chapter::create([
        'title' => 'Métricas Clave',
        'description' => 'Cómo medir el éxito de campañas digitales.',
        'module_id' => 9,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Google Analytics',
        'description' => 'Uso de Google Analytics para análisis.',
        'module_id' => 9,
        'order' => 2,
    ]);

    // Course 5: Introducción a la Inteligencia Artificial (Module IDs 10, 11, 12) - 3 modules, 5 chapters
    // Module 10: Conceptos Básicos de IA
    Chapter::create([
        'title' => '¿Qué es la IA?',
        'description' => 'Video introductorio sobre inteligencia artificial.',
        'module_id' => 10,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Historia de la IA',
        'description' => 'Evolución de la inteligencia artificial.',
        'module_id' => 10,
        'order' => 2,
    ]);
    // Module 11: Algoritmos de Machine Learning
    Chapter::create([
        'title' => 'Regresión Lineal',
        'description' => 'Introducción a algoritmos de regresión.',
        'module_id' => 11,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Clasificación',
        'description' => 'Técnicas de clasificación en machine learning.',
        'module_id' => 11,
        'order' => 2,
    ]);
    // Module 12: Aplicaciones Prácticas de IA
    Chapter::create([
        'title' => 'IA en la Vida Real',
        'description' => 'Casos de uso de IA en industrias.',
        'module_id' => 12,
        'order' => 1,
    ]);

    // Course 6: Análisis de Datos con Python (Module IDs 13, 14, 15) - 3 modules, 5 chapters
    // Module 13: Introducción a Python
    Chapter::create([
        'title' => 'Sintaxis de Python',
        'description' => 'Conceptos básicos de programación en Python.',
        'module_id' => 13,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Estructuras de Datos',
        'description' => 'Listas, diccionarios y tuplas en Python.',
        'module_id' => 13,
        'order' => 2,
    ]);
    // Module 14: Análisis de Datos con Pandas
    Chapter::create([
        'title' => 'Introducción a Pandas',
        'description' => 'Uso de Pandas para manipulación de datos.',
        'module_id' => 14,
        'order' => 1,
    ]);
    Chapter::create([
        'title' => 'Limpieza de Datos',
        'description' => 'Técnicas para limpiar datasets con Pandas.',
        'module_id' => 14,
        'order' => 2,
    ]);
    // Module 15: Visualización de Datos
    Chapter::create([
        'title' => 'Gráficos con Matplotlib',
        'description' => 'Creación de visualizaciones con Matplotlib.',
        'module_id' => 15,
        'order' => 1,
    ]);

    // Course 7: Finanzas Personales (Module IDs 16, 17) - 2 modules, 2 chapters
    // Module 16: Gestión de Presupuestos
    Chapter::create([
        'title' => 'Creación de Presupuestos',
        'description' => 'Cómo planificar un presupuesto personal.',
        'module_id' => 16,
        'order' => 1,
    ]);
    // Module 17: Introducción a Inversiones
    Chapter::create([
        'title' => 'Conceptos de Inversión',
        'description' => 'Introducción a los tipos de inversiones.',
        'module_id' => 17,
        'order' => 1,
    ]);

    // Course 8: Inglés Conversacional (Module IDs 18, 19) - 2 modules, 2 chapters
    // Module 18: Vocabulario Básico
    Chapter::create([
        'title' => 'Frases Comunes',
        'description' => 'Vocabulario esencial para conversaciones.',
        'module_id' => 18,
        'order' => 1,
    ]);
    // Module 19: Conversación Práctica
    Chapter::create([
        'title' => 'Diálogos Reales',
        'description' => 'Práctica de diálogos en inglés.',
        'module_id' => 19,
        'order' => 1,
    ]);

    // Course 9: Fotografía Digital (Module IDs 20, 21) - 2 modules, 2 chapters
    // Module 20: Técnicas de Fotografía
    Chapter::create([
        'title' => 'Uso de la Cámara',
        'description' => 'Configuración básica de cámaras digitales.',
        'module_id' => 20,
        'order' => 1,
    ]);
    // Module 21: Edición de Imágenes
    Chapter::create([
        'title' => 'Edición con Lightroom',
        'description' => 'Técnicas de edición en Adobe Lightroom.',
        'module_id' => 21,
        'order' => 1,
    ]);

    // Course 10: Edición de Video Profesional (Module IDs 22, 23) - 2 modules, 2 chapters
    // Module 22: Fundamentos de Edición
    Chapter::create([
        'title' => 'Corte y Montaje',
        'description' => 'Técnicas básicas de edición de video.',
        'module_id' => 22,
        'order' => 1,
    ]);
    // Module 23: Efectos y Postproducción
    Chapter::create([
        'title' => 'Efectos Visuales',
        'description' => 'Uso de efectos en Adobe After Effects.',
        'module_id' => 23,
        'order' => 1,
    ]);

    // Course 11: Yoga y Mindfulness (Module IDs 24, 25) - 2 modules, 2 chapters
    // Module 24: Técnicas de Yoga
    Chapter::create([
        'title' => 'Posturas Básicas',
        'description' => 'Introducción a posturas de yoga.',
        'module_id' => 24,
        'order' => 1,
    ]);
    // Module 25: Prácticas de Mindfulness
    Chapter::create([
        'title' => 'Meditación Guiada',
        'description' => 'Ejercicios de mindfulness para principiantes.',
        'module_id' => 25,
        'order' => 1,
    ]);
    }
}
