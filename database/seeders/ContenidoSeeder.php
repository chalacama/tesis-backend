<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Chapter;
use App\Models\LearningContent;
use App\Models\Test;
use App\Models\Question;
use App\Models\Answer;
class ContenidoSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Curso objetivo: id 2 (Fundamentos de Diseño Gráfico)

		// 1) Crear módulos exclusivos para el curso 2
		$unidadA = Module::create([
			'name' => 'Unidad A',
			'order' => 1,
			'course_id' => 2,
		]);

		$unidadFinal = Module::create([
			'name' => 'Unidad Final',
			'order' => 2,
			'course_id' => 2,
		]);

		// 2) Capítulos para Unidad A (2 capítulos)
		$intro = Chapter::create([
			'title' => 'Introducción a Fundamentos de Diseño Gráfico',
			'description' => 'Video introductorio: conceptos clave y recorrido del curso.',
			'module_id' => $unidadA->id,
			'order' => 1,
		]);

		$desarrollo = Chapter::create([
			'title' => 'Desarrollo',
			'description' => 'Documento con la teoría y ejercicios (PDF).',
			'module_id' => $unidadA->id,
			'order' => 2,
		]);

		// 3) Capítulo para Unidad Final (1 capítulo)
		$evaluacion = Chapter::create([
			'title' => 'Evaluación final',
			'description' => 'Prueba final para evaluar lo aprendido en el curso.',
			'module_id' => $unidadFinal->id,
			'order' => 1,
		]);

		// 4) Contenidos de aprendizaje
		// Intro: video (link - YouTube)
		LearningContent::create([
			'name' => 'Video: Bienvenida y conceptos básicos',
			'url' => 'https://www.youtube.com/watch?v=E-DDmIhL4IM',
			'type_content_id' => 1, // link
			'format_id' => 1, // youtube
			'chapter_id' => $intro->id,
			'duration_seconds' => 300,
		]);

		// Desarrollo: PDF (archivo)
		LearningContent::create([
			'name' => 'Desarrollo - Guía y ejercicios.pdf',
			'url' => 'https://drive.google.com/file/d/1M002KXApMguRwNZMHiegucHD-vonsfin/view?usp=sharing',
            'url_insert' => 'https://drive.google.com/file/d/1M002KXApMguRwNZMHiegucHD-vonsfin/preview',
			'type_content_id' => 1, // link
			'format_id' => 12, // googledrive.pdf
			'chapter_id' => $desarrollo->id,
			'duration_seconds' => null,
            'size_bytes' => 10485760, // 10 MB
		]);

		// 5) Crear Test final vinculado al capítulo de evaluación
		$test = Test::create([
			'chapter_id' => $evaluacion->id,
			'random' => false,
			'incorrect' => true,
			'score' => true,
			'split' => 1,
			'limited' => 0,
		]);

		// 6) Preguntas y respuestas (5 preguntas, opción múltiple)
		$q1 = Question::create([
			'statement' => '¿Qué elemento es clave en la composición visual?',
			'spot' => 1,
			'order' => 1,
			'type_questions_id' => 1,
			'test_id' => $test->id,
		]);
		Answer::create(['option' => 'Balance', 'is_correct' => true, 'order' => 1, 'question_id' => $q1->id]);
		Answer::create(['option' => 'Servidor web', 'is_correct' => false, 'order' => 2, 'question_id' => $q1->id]);
		Answer::create(['option' => 'Query SQL', 'is_correct' => false, 'order' => 3, 'question_id' => $q1->id]);
		Answer::create(['option' => 'Tiempo de carga', 'is_correct' => false, 'order' => 4, 'question_id' => $q1->id]);

		$q2 = Question::create([
			'statement' => '¿Cuál es la unidad básica del color en impresión?',
			'spot' => 1,
			'order' => 2,
			'type_questions_id' => 1,
			'test_id' => $test->id,
		]);
		Answer::create(['option' => 'CMYK', 'is_correct' => true, 'order' => 1, 'question_id' => $q2->id]);
		Answer::create(['option' => 'RGB', 'is_correct' => false, 'order' => 2, 'question_id' => $q2->id]);
		Answer::create(['option' => 'HEX', 'is_correct' => false, 'order' => 3, 'question_id' => $q2->id]);
		Answer::create(['option' => 'Píxel', 'is_correct' => false, 'order' => 4, 'question_id' => $q2->id]);

		$q3 = Question::create([
			'statement' => '¿Qué tipografía conviene para titulares impactantes?',
			'spot' => 1,
			'order' => 3,
			'type_questions_id' => 1,
			'test_id' => $test->id,
		]);
		Answer::create(['option' => 'Sans serif en negrita', 'is_correct' => true, 'order' => 1, 'question_id' => $q3->id]);
		Answer::create(['option' => 'Monoespaciada', 'is_correct' => false, 'order' => 2, 'question_id' => $q3->id]);
		Answer::create(['option' => 'Cursiva ligera', 'is_correct' => false, 'order' => 3, 'question_id' => $q3->id]);
		Answer::create(['option' => 'Fuente de sistema por defecto', 'is_correct' => false, 'order' => 4, 'question_id' => $q3->id]);

		$q4 = Question::create([
			'statement' => '¿Qué formato es el más adecuado para un póster impreso de alta calidad?',
			'spot' => 1,
			'order' => 4,
			'type_questions_id' => 1,
			'test_id' => $test->id,
		]);
		Answer::create(['option' => 'PDF con sangrado y alta resolución', 'is_correct' => true, 'order' => 1, 'question_id' => $q4->id]);
		Answer::create(['option' => 'JPEG web optimizado', 'is_correct' => false, 'order' => 2, 'question_id' => $q4->id]);
		Answer::create(['option' => 'SVG sin conversión', 'is_correct' => false, 'order' => 3, 'question_id' => $q4->id]);
		Answer::create(['option' => 'TXT plano', 'is_correct' => false, 'order' => 4, 'question_id' => $q4->id]);

		$q5 = Question::create([
			'statement' => '¿Cuál es una buena práctica al exportar imágenes para la web?',
			'spot' => 1,
			'order' => 5,
			'type_questions_id' => 1,
			'test_id' => $test->id,
		]);
		Answer::create(['option' => 'Comprimir sin perder legibilidad y usar formatos modernos', 'is_correct' => true, 'order' => 1, 'question_id' => $q5->id]);
		Answer::create(['option' => 'Exportar siempre en BMP', 'is_correct' => false, 'order' => 2, 'question_id' => $q5->id]);
		Answer::create(['option' => 'Subir imágenes sin optimizar', 'is_correct' => false, 'order' => 3, 'question_id' => $q5->id]);
		Answer::create(['option' => 'Usar únicamente PNG para todo', 'is_correct' => false, 'order' => 4, 'question_id' => $q5->id]);
	}

}