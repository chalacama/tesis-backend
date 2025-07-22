<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    Category::create([
        'name' => 'Programación', // id: 1
        'description' => 'Cursos sobre lenguajes de programación y desarrollo de software.',
    ]);
    Category::create([
        'name' => 'Diseño', // id: 2
        'description' => 'Cursos sobre diseño gráfico, digital y experiencia de usuario.',
    ]);
    Category::create([
        'name' => 'Marketing', // id: 3
        'description' => 'Cursos sobre estrategias de marketing digital y tradicional.',
    ]);
    Category::create([
        'name' => 'Negocios', // id: 4
        'description' => 'Cursos sobre gestión empresarial, emprendimiento y liderazgo.',
    ]);
    Category::create([
        'name' => 'Tecnología', // id: 5
        'description' => 'Cursos sobre innovaciones tecnológicas y herramientas digitales.',
    ]);
    Category::create([
        'name' => 'Ciencia de Datos', // id: 6
        'description' => 'Cursos sobre análisis de datos, estadísticas y visualización.',
    ]);
    Category::create([
        'name' => 'Finanzas', // id: 7
        'description' => 'Cursos sobre gestión financiera, inversiones y economía.',
    ]);
    Category::create([
        'name' => 'Idiomas', // id: 8
        'description' => 'Cursos para aprender y perfeccionar habilidades lingüísticas.',
    ]);
    Category::create([
        'name' => 'Fotografía', // id: 9
        'description' => 'Cursos sobre técnicas fotográficas y edición de imágenes.',
    ]);
    Category::create([
        'name' => 'Video y Animación', // id: 10
        'description' => 'Cursos sobre producción de video, animación y multimedia.',
    ]);
    Category::create([
        'name' => 'Salud y Bienestar', // id: 11
        'description' => 'Cursos sobre fitness, nutrición y salud mental.',
    ]);
    Category::create([
        'name' => 'Música', // id: 12
        'description' => 'Cursos sobre instrumentos, teoría musical y producción.',
    ]);
    Category::create([
        'name' => 'Escritura', // id: 13
        'description' => 'Cursos sobre redacción creativa, técnica y profesional.',
    ]);
    Category::create([
        'name' => 'Educación', // id: 14
        'description' => 'Cursos sobre pedagogía, enseñanza y aprendizaje.',
    ]);
    Category::create([
        'name' => 'Cocina', // id: 15
        'description' => 'Cursos sobre técnicas culinarias y gastronomía.',
    ]);
    Category::create([
        'name' => 'Arte', // id: 16
        'description' => 'Cursos sobre pintura, dibujo y otras formas de arte.',
    ]);
    Category::create([
        'name' => 'Medio Ambiente', // id: 17
        'description' => 'Cursos sobre sostenibilidad y ciencias ambientales.',
    ]);
    Category::create([
        'name' => 'Habilidades Personales', // id: 18
        'description' => 'Cursos sobre comunicación, liderazgo y desarrollo personal.',
    ]);
    Category::create([
        'name' => 'Ingeniería', // id: 19
        'description' => 'Cursos sobre ingeniería de software, mecánica y más.',
    ]);
    Category::create([
        'name' => 'Ciencias', // id: 20
        'description' => 'Cursos sobre biología, química, física y otras ciencias.',
    ]);
    }
}
