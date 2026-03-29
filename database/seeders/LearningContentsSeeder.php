<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LearningContent;

/**
 * Referencia de format_id (ver TypeLearningContentSeeder):
 *
 *  1  youtube          |  10 googledrive.video |  18 onedrive.video 
 *  2  pdf              |  11 googledrive.audio |  19 onedrive.audio
 *  3  video            |  12 googledrive.pdf   |  20 onedrive.pdf
 *  4  audio            |  13 googledrive.docx  |  21 onedrive.docx
 *  5  docx             |  14 googledrive.pptx  |  22 onedrive.pptx
 *  6  pptx             |  15 googledrive.xlsx  |  23 onedrive.xlsx
 *  7  xlsx             |  16 googledrive.zip   |  24 onedrive.zip
 *  8  zip              |  17 googledrive.txt   |  25 onedrive.txt
 *  9  txt
 *
 * type_content_id:  1 = link  |  2 = archive
 */
class LearningContentsSeeder extends Seeder
{
    public function run(): void
    {
        // ────────────────────────────────────────────────────────────────────
        // ARCHIVE (type_content_id: 2) — capítulos 1 – 12
        // ────────────────────────────────────────────────────────────────────

        // Chapter 1 — mp4 (Cloudinary)
        LearningContent::create([
            'name'             => 'Welcome to Laravel.mp4',
            'url'              => 'https://storage.googleapis.com/mi-app-storage/courses/1/chapters/content/Welcome%20to%20Laravel.mp4',
            'type_content_id'  => 2,
            'chapter_id'       => 1,
            'format_id'        => 3,       // video
            'size_bytes'       => 134217728, // 13 MB
            'duration_seconds' => 126,
        ]);

        // Chapter 3 — pdf (Cloudinary)
        LearningContent::create([
            'name'             => 'Configuraciones-Laravel.pdf',
            'url'              => 'https://storage.googleapis.com/mi-app-storage/courses/1/chapters/3/content/Configuraciones-Laravel.pdf',
            'type_content_id'  => 2,
            'chapter_id'       => 3,
            'format_id'        => 2,       // pdf
            'size_bytes'       => 4035040, //4 MB
            'duration_seconds' => null,
        ]);

        // Chapter 7 — docx (Cloudinary raw)
        LearningContent::create([
            'name'             => 'ARTICULO_129_-_CHALA_1_lhqwv2.docx',
            'url'              => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773475782/ARTICULO_129_-_CHALA_1_lhqwv2.docx',
            'type_content_id'  => 2,
            'chapter_id'       => 7,
            'format_id'        => 5,       // docx
            'size_bytes'       => 87856,
            'duration_seconds' => null,
        ]);

        // Chapter 8 — pptx (Cloudinary raw)
        LearningContent::create([
            'name'             => 'FORMATO_PRESENTACIO_N_TIC_tpg6mu.pptx',
            'url'              => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773476522/FORMATO_PRESENTACIO%CC%81N_TIC_tpg6mu.pptx',
            'type_content_id'  => 2,
            'chapter_id'       => 8,
            'format_id'        => 6,       // pptx
            'size_bytes'       => 78140,
            'duration_seconds' => null,
        ]);

        // Chapter 9 — xlsx (Cloudinary raw)
        LearningContent::create([
            'name'             => 'Tabla_de_correciones_tesis_h8bzx2.xlsx',
            'url'              => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773458690/Tabla_de_correciones_tesis_h8bzx2.xlsx',
            'type_content_id'  => 2,
            'chapter_id'       => 9,
            'format_id'        => 7,       // xlsx
            'size_bytes'       => 45906,
            'duration_seconds' => null,
        ]);

        // Chapter 10 — zip (Cloudinary raw)
        LearningContent::create([
            'name'             => 'gravestone-forge-1.20.1-1.0.35.zip',
            'url'              => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773458582/gravestone-forge-1.20.1-1.0.35_dh9jzk.zip',
            'type_content_id'  => 2,
            'chapter_id'       => 10,
            'format_id'        => 8,       // zip
            'size_bytes'       => 289748,
            'duration_seconds' => null,
        ]);

        // Chapter 11 — mp3 (Cloudinary video)
        LearningContent::create([
            'name'             => 'ssstik.io_1768127717922.mp3',
            'url'              => 'https://res.cloudinary.com/dvvqko1vv/video/upload/v1773476999/ssstik.io_1768127717922_e2j49d.mp3',
            'type_content_id'  => 2,
            'chapter_id'       => 11,
            'format_id'        => 4,       // mp3
            'size_bytes'       => 535450,
            'duration_seconds' => 33,
        ]);

        // Chapter 12 — txt (Cloudinary raw)
        LearningContent::create([
            'name'             => 'Explanation.txt',
            'url'              => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773958110/Explanation_bhvyig.txt',
            'type_content_id'  => 2,
            'chapter_id'       => 12,
            'format_id'        => 9,       // txt
            'size_bytes'       => 5283,
            'duration_seconds' => null,
        ]);

        // ────────────────────────────────────────────────────────────────────
        // LINK — YouTube (type_content_id: 1, format_id: 1)
        // ────────────────────────────────────────────────────────────────────

        // Chapter 2 — youtube
        LearningContent::create([
            'name'             => null,
            'url'              => 'https://www.youtube.com/watch?v=_Rsen6614Dg',
            'type_content_id'  => 1,
            'chapter_id'       => 2,
            'format_id'        => 1,       // youtube
            'size_bytes'       => null,
            'duration_seconds' => 383,
        ]);

        // ────────────────────────────────────────────────────────────────────
        // LINK — Google Drive (type_content_id: 1, format_ids: 10 – 17)
        // ────────────────────────────────────────────────────────────────────

        // Chapter 13 — googledrive.mp4
        LearningContent::create([
            'name'             => 'eez-ppyu-pti (2021-07-03 at 11:38 GMT-7).mp4',
            'url'              => 'https://drive.google.com/file/d/1iD9qwCh6svXdTYLa90k6CBg9wO057D7g/preview',
            'type_content_id'  => 1,
            'chapter_id'       => 13,
            'format_id'        => 10,      // googledrive.video
            'size_bytes'       => null,
            'duration_seconds' => 20,
        ]);

        // Chapter 14 — googledrive.mp3
        LearningContent::create([
            'name'             => 'Software Público Ecuatoriano_ Derivados y Privacidad.wav',
            'url'              => 'https://drive.google.com/file/d/1tUk0nKWxKIgDUTS89H9WSF6F5MBn5ej3/preview',
            'type_content_id'  => 1,
            'chapter_id'       => 14,
            'format_id'        => 11,      // googledrive.audio
            'size_bytes'       => null,
            'duration_seconds' => 284,
        ]);

        // Chapter 15 — googledrive.pdf
        LearningContent::create([
            'name'             => '02- Circuitos Lógicos - Algebra de Boole - Síntesis de Funciones Lógicas.pdf',
            'url'              => 'https://drive.google.com/file/d/16Z-LnREYVdMrleCOkp0v0pxaPq2KLISM/preview',
            'type_content_id'  => 1,
            'chapter_id'       => 15,
            'format_id'        => 12,      // googledrive.pdf
            'size_bytes'       => 206000,
            'duration_seconds' => null,
        ]);

        // Chapter 16 — googledrive.docx
        LearningContent::create([
            'name'             => 'UNIDAD 3 - ACTIVIDAD FORMATIVA.docx',
            'url'              => 'https://docs.google.com/document/d/1lYULSjsdqUunNAGAvaGcqen2bMkL4V23s9Szf1pCCZ0/preview',
            'type_content_id'  => 1,
            'chapter_id'       => 16,
            'format_id'        => 13,      // googledrive.docx
            'size_bytes'       => 62464,
            'duration_seconds' => null,
        ]);

        // Chapter 17 — googledrive.pptx
        LearningContent::create([
            'name'             => 'CC-0405 Estadística - Capítulo 2 - Probabilidad',
            'url'              => 'https://docs.google.com/presentation/d/1_sd9Pfi1LCHpC-Kx8qRlChyNpdzDwRXQndFCPect_Bk/preview',
            'type_content_id'  => 1,
            'chapter_id'       => 17,
            'format_id'        => 14,      // googledrive.pptx
            'size_bytes'       => 19200000,
            'duration_seconds' => null,
        ]);

        // Chapter 18 — googledrive.xlsx
        LearningContent::create([
            'name'             => 'CC-0405 Estadística - Capítulo 2 - Probabilidad (xlsx)',
            'url'              => 'https://docs.google.com/spreadsheets/d/1_sd9Pfi1LCHpC-Kx8qRlChyNpdzDwRXQndFCPect_Bk/preview',
            'type_content_id'  => 1,
            'chapter_id'       => 18,
            'format_id'        => 15,      // googledrive.xlsx
            'size_bytes'       => 62464,
            'duration_seconds' => null,
        ]);

        // Chapter 19 — googledrive.zip
        LearningContent::create([
            'name'             => 'mods fabric 1.21.11.zip',
            'url'              => 'https://drive.google.com/file/d/11eC07_vBHYM9R5soLn3xG5IaBhFaog-p/preview',
            'type_content_id'  => 1,
            'chapter_id'       => 19,
            'format_id'        => 16,      // googledrive.zip
            'size_bytes'       => 10240000,
            'duration_seconds' => null,
        ]);

        // Chapter 20 — googledrive.txt
        LearningContent::create([
            'name'             => 'latest-error.txt',
            'url'              => 'https://drive.google.com/file/d/1RuS1-ZlHjjbpyAI1qq1pd2-OGKFr7uQi/preview',
            'type_content_id'  => 1,
            'chapter_id'       => 20,
            'format_id'        => 17,      // googledrive.txt
            'size_bytes'       => null,
            'duration_seconds' => null,
        ]);

        // ────────────────────────────────────────────────────────────────────
        // LINK — OneDrive (type_content_id: 1, format_ids: 18 – 25)
        // ────────────────────────────────────────────────────────────────────

        // Chapter 21 — onedrive.mp4
        LearningContent::create([
            'name'             => 'S03E08.2025 Latino - 2.mp4',
            'url'              => 'https://1drv.ms/v/c/904b18bca3295b5a/IQSDt_a5n0PKTok5yJiIZqEUAR1cKIpBLb5f_l-6bvM8eLs?width=1280&height=720',
            'type_content_id'  => 1,
            'chapter_id'       => 21,
            'format_id'        => 18,      // onedrive.video
            'size_bytes'       => null,
            'duration_seconds' => 280,
        ]);

        // Chapter 22 — onedrive.mp3
        LearningContent::create([
            'name'             => 'VFwmKL5OL-Q_mp3',
            'url'              => 'https://1drv.ms/u/c/904b18bca3295b5a/IQRvJW0pKq50SKH3liUoJsTqAeHWBsqeGQF0Obi9Q0zMqFw',
            'type_content_id'  => 1,
            'chapter_id'       => 22,
            'format_id'        => 19,      // onedrive.audio
            'size_bytes'       => null,
            'duration_seconds' => 244,
        ]);

        // Chapter 23 — onedrive.pdf
        LearningContent::create([
            'name'             => 'entregables_objetivo_1-2.pdf',
            'url'              => 'https://1drv.ms/b/c/904b18bca3295b5a/IQSJeRgwxcZJSYyiaesNy4zAASH1T5amUKDJnOL0STZgsC8',
            'type_content_id'  => 1,
            'chapter_id'       => 23,
            'format_id'        => 20,      // onedrive.pdf
            'size_bytes'       => 3782386,
            'duration_seconds' => null,
        ]);

        // Chapter 24 — onedrive.docx
        LearningContent::create([
            'name'             => 'INFORME TÉCNICO.docx',
            'url'              => 'https://1drv.ms/w/c/904b18bca3295b5a/IQSfrOCS6eX4TouGBMiEGGRVARlUjroz94tIVBi-hMYwjGw',
            'type_content_id'  => 1,
            'chapter_id'       => 24,
            'format_id'        => 21,      // onedrive.docx
            'size_bytes'       => 17700,
            'duration_seconds' => null,
        ]);

        // Chapter 25 — onedrive.pptx
        LearningContent::create([
            'name'             => 'Influencia-de-la-Exclusion-Social-en-la-Adiccion-a-Redes-Sociales-en-Adolescentes.pptx',
            'url'              => 'https://1drv.ms/p/c/904b18bca3295b5a/IQT8TVCNorU6TKcgoTLGjpslAV7meOP2xhlApk75AH98wYg',
            'type_content_id'  => 1,
            'chapter_id'       => 25,
            'format_id'        => 22,      // onedrive.pptx
            'size_bytes'       => 19800,
            'duration_seconds' => null,
        ]);

        // Chapter 26 — onedrive.xlsx
        LearningContent::create([
            'name'             => 'Cambios produ.xlsx',
            'url'              => 'https://1drv.ms/x/c/904b18bca3295b5a/IQQ8iBR4rj4PRYH23GQXPaIaARqAQxVgd6804H6l4MTD2cc',
            'type_content_id'  => 1,
            'chapter_id'       => 26,
            'format_id'        => 23,      // onedrive.xlsx
            'size_bytes'       => 19800,
            'duration_seconds' => null,
        ]);

        // Chapter 27 — onedrive.zip
        LearningContent::create([
            'name'             => 'illustration.zip',
            'url'              => 'https://1drv.ms/u/c/904b18bca3295b5a/IQSNA4euPrWqT5RZ3o7JdlHeAZMIdFqalyFdOxrawRQA2Mg',
            'type_content_id'  => 1,
            'chapter_id'       => 27,
            'format_id'        => 24,      // onedrive.zip
            'size_bytes'       => 2498688,
            'duration_seconds' => null,
        ]);

        // Chapter 28 — onedrive.txt
        LearningContent::create([
            'name'             => 'hola.txt',
            'url'              => 'https://1drv.ms/t/c/904b18bca3295b5a/IQQOcBtaAyM_QraDEdmtgRxbAazhTrwF2c1GC5Bj4Bb-Who',
            'type_content_id'  => 1,
            'chapter_id'       => 28,
            'format_id'        => 25,      // onedrive.txt
            'size_bytes'       => 338,
            'duration_seconds' => null,
        ]);
    }
}