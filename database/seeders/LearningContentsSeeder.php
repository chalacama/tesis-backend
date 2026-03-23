<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LearningContent;
use Illuminate\Support\Facades\DB;
class LearningContentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // 'youtube', //1
        // 'pdf', //2
        // 'mp4', //3
        // 'mp3', //4
        // 'docx', //5
        // 'pptx', //6
        // 'xlsx', //7
        // 'zip', //8
        // 'txt', //9
        // 'googledrive.mp4', //10
        // 'googledrive.mp3', //11
        // 'googledrive.pdf', //12
        // 'googledrive.docx', //13
        // 'googledrive.pptx', //14
        // 'googledrive.xlsx', //15
        // 'googledrive.zip', //16
        // 'googledrive.txt', //17
        // 'onedrive.mp4', //18
        // 'onedrive.mp3', //19
        // 'onedrive.pdf', //20
        // 'onedrive.docx', //21
        // 'onedrive.pptx', //22
        // 'onedrive.xlsx', //23
        // 'onedrive.zip', //24
        // 'onedrive.txt', //25
        LearningContent::create([
            'name' => '1.mp4',
            'url' => 'https://res.cloudinary.com/dvvqko1vv/video/upload/v1764845297/archives/chapter/1.mp4',
            'type_content_id' => 2,
            'chapter_id' => 1,
            'format_id' => 3, // mp4
            'size_bytes' => 5188650, //5.188.650 bytes
            'duration_seconds' => 126,
        ]);
        LearningContent::create([
            'url' => 'https://www.youtube.com/watch?v=_Rsen6614Dg',
            'type_content_id' => 1,
            'chapter_id' => 2,
            'format_id' => 1, // youtube
            'duration_seconds' => 383,
            'size_bytes' => null,
        ]);
        LearningContent::create([
            'name' => '3.pdf',
            'url' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1764846842/archives/chapter/3.pdf',
            'type_content_id' => 2,
            'chapter_id' => 3,
            'format_id' => 2, // pdf
            'size_bytes' => 4035040, //4.035.040 bytes
            'duration_seconds' => null,
        ]);

        // ✅ CORRECTO: chapter 7 = word → docx
        LearningContent::create([
            'name' => 'ARTICULO_129_-_CHALA_1_lhqwv2.docx',
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773475782/ARTICULO_129_-_CHALA_1_lhqwv2.docx',
            'type_content_id' => 2,
            'chapter_id' => 7,   // Archivo editable word
            'format_id' => 5,    // docx
            'size_bytes' => 87856, //87.856 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'FORMATO_PRESENTACIO_N_TIC_tpg6mu.pptx',
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773476522/FORMATO_PRESENTACIO%CC%81N_TIC_tpg6mu.pptx',
            'type_content_id' => 2,
            'chapter_id' => 8,   // Archivo editable pptx
            'format_id' => 6,    // pptx
            'size_bytes' => 78140, //78.140 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'Tabla_de_correciones_tesis_h8bzx2.xlsx',
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773458690/Tabla_de_correciones_tesis_h8bzx2.xlsx',
            'type_content_id' => 2,
            'chapter_id' => 9,   // Archivo editable excel
            'format_id' => 7,    // xlsx
            'size_bytes' => 45906, //45.906 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'gravestone-forge-1.20.1-1.0.35.zip',
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773458582/gravestone-forge-1.20.1-1.0.35_dh9jzk.zip',
            'type_content_id' => 2,
            'chapter_id' => 10,  // Archivo comprimido
            'format_id' => 8,    // zip
            'size_bytes' => 289748, //289.748 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'ssstik.io_1768127717922.mp3',
            'url' => 'https://res.cloudinary.com/dvvqko1vv/video/upload/v1773476999/ssstik.io_1768127717922_e2j49d.mp3',
            'type_content_id' => 2,
            'chapter_id' => 11,
            'format_id' => 4, // mp3
            'size_bytes' => 535450, //535.450 bytes
            'duration_seconds' => 33,
        ]);
        LearningContent::create([
            'name' => 'Explanation.txt',
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773958110/Explanation_bhvyig.txt',
            'type_content_id' => 2,
            'chapter_id' => 12,
            'format_id' => 9, // txt
            'size_bytes' => 5283, //5.283 bytes
            'duration_seconds' => null,
        ]);
        //googledrive
        LearningContent::create([
            'name' => 'eez-ppyu-pti (2021-07-03 at 11:38 GMT-7).mp4',
            'url' => 'https://drive.google.com/file/d/1iD9qwCh6svXdTYLa90k6CBg9wO057D7g/preview',
            'type_content_id' => 1,
            'chapter_id' => 13,
            'format_id' => 10, // googledrive.mp4
            'size_bytes' => null, // 1.1 MB
            'duration_seconds' => 20, //00:20
        ]);
        LearningContent::create([
            'name' => 'Software Público Ecuatoriano_ Derivados y Privacidad.wav',
            'url' => 'https://drive.google.com/file/d/1tUk0nKWxKIgDUTS89H9WSF6F5MBn5ej3/preview',
            'type_content_id' => 1,
            'chapter_id' => 14,
            'format_id' => 11, // googledrive.mp3
            'size_bytes' => null, //9.34 MB
            'duration_seconds' => 284, //04:44
        ]);
        LearningContent::create([
            'name' => '02- Circuitos Lógicos - Algebra de Boole - Síntesis de Funciones Lógicas.pdf',
            'url' => 'https://drive.google.com/file/d/16Z-LnREYVdMrleCOkp0v0pxaPq2KLISM/preview',
            'type_content_id' => 1,
            'chapter_id' => 15,
            'format_id' => 12, // googledrive.pdf
            'size_bytes' => 206000, //206 KB
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'UNIDAD 3 - ACTIVIDAD FORMATIVA.docx',
            'url' => 'https://docs.google.com/document/d/1lYULSjsdqUunNAGAvaGcqen2bMkL4V23s9Szf1pCCZ0/preview',
            'type_content_id' => 1,
            'chapter_id' => 16,
            'format_id' => 13, // googledrive.docx
            'size_bytes' => 62464, //61 KB bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'CC-0405 Estadística - Capítulo 2 - Probabilidad',
            'url' => 'https://docs.google.com/presentation/d/1_sd9Pfi1LCHpC-Kx8qRlChyNpdzDwRXQndFCPect_Bk/preview',
            'type_content_id' => 1,
            'chapter_id' => 17,
            'format_id' => 14, // googledrive.pptx
            'size_bytes' => 19200000, //19.2 MB
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'CC-0405 Estadística - Capítulo 2 - Probabilidad',
            'url' => 'https://docs.google.com/presentation/d/1_sd9Pfi1LCHpC-Kx8qRlChyNpdzDwRXQndFCPect_Bk/preview',
            'type_content_id' => 1,
            'chapter_id' => 18,
            'format_id' => 15, // googledrive.xlsx
            'size_bytes' => 62464, //61 KB
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'mods fabric 1.21.11.zip',
            'url' => 'https://drive.google.com/file/d/11eC07_vBHYM9R5soLn3xG5IaBhFaog-p/preview',
            'type_content_id' => 1,
            'chapter_id' => 19,
            'format_id' => 16, // googledrive.zip
            'size_bytes' => 10240000, //9.8 MB
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'latest-error.txt',
            'url' => 'https://drive.google.com/file/d/1RuS1-ZlHjjbpyAI1qq1pd2-OGKFr7uQi/preview',
            'type_content_id' => 1,
            'chapter_id' => 20,
            'format_id' => 17, // googledrive.txt
            'size_bytes' => 10240000, //9.8 MB
            'duration_seconds' => null,
        ]);
        //onedrive
        LearningContent::create([
            'name' => 'S03E08.2025 Latino - 2.mp4',
            'url' => 'https://1drv.ms/v/c/904b18bca3295b5a/IQSDt_a5n0PKTok5yJiIZqEUAR1cKIpBLb5f_l-6bvM8eLs?width=1280&height=720',
            'type_content_id' => 1,
            'chapter_id' => 21,
            'format_id' => 18, // onedrive.mp4
            'size_bytes' => null, // 61,3 MB
            'duration_seconds' => 280, //04:40
        ]);
        LearningContent::create([
            'name' => 'VFwmKL5OL-Q_mp3',
            'url' => 'https://1drv.ms/u/c/904b18bca3295b5a/IQRvJW0pKq50SKH3liUoJsTqAeHWBsqeGQF0Obi9Q0zMqFw',
            'type_content_id' => 1,
            'chapter_id' => 22,
            'format_id' => 19, // onedrive.mp3
            'size_bytes' => null, //9,34 mb
            'duration_seconds' => 244, //04:04
        ]);
        LearningContent::create([
            'name' => 'entregables_objetivo_1-2.pdf',
            'url' => 'https://1drv.ms/b/c/904b18bca3295b5a/IQSJeRgwxcZJSYyiaesNy4zAASH1T5amUKDJnOL0STZgsC8',
            'type_content_id' => 1,
            'chapter_id' => 23,
            'format_id' => 20, // onedrive.pdf
            'size_bytes' => 3782386, //3.782.386 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'INFORME TÉCNICO.docx',
            'url' => 'https://1drv.ms/w/c/904b18bca3295b5a/IQSfrOCS6eX4TouGBMiEGGRVARlUjroz94tIVBi-hMYwjGw',
            'type_content_id' => 1,
            'chapter_id' => 24,
            'format_id' => 21, // onedrive.docx
            'size_bytes' => 17700, //17,7 kb
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'INFORME TÉCNICO.docx',
            'url' => '"https://1drv.ms/x/c/904b18bca3295b5a/IQQ8iBR4rj4PRYH23GQXPaIaARqAQxVgd6804H6l4MTD2cc',
            'type_content_id' => 1,
            'chapter_id' => 25,
            'format_id' => 22, // onedrive.pptx
            'size_bytes' => 19800, //19,8 kb
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'INFORME TÉCNICO.docx',
            'url' => '"https://1drv.ms/x/c/904b18bca3295b5a/IQQ8iBR4rj4PRYH23GQXPaIaARqAQxVgd6804H6l4MTD2cc',
            'type_content_id' => 1,
            'chapter_id' => 26,
            'format_id' => 23, // onedrive.xlsx
            'size_bytes' => 19800, //19,8 kb
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'illustration.zip',
            'url' => '"https://1drv.ms/u/c/904b18bca3295b5a/IQSNA4euPrWqT5RZ3o7JdlHeAZMIdFqalyFdOxrawRQA2Mg',
            'type_content_id' => 1,
            'chapter_id' => 27,
            'format_id' => 24, // onedrive.zip
            'size_bytes' => 2498688, //2,39 mb
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'name' => 'hola.txt',
            'url' => '"https://1drv.ms/t/c/904b18bca3295b5a/IQQOcBtaAyM_QraDEdmtgRxbAazhTrwF2c1GC5Bj4Bb-Who',
            'type_content_id' => 1,
            'chapter_id' => 28,
            'format_id' => 25, // onedrive.txt
            'size_bytes' => 338, //338 bytes
            'duration_seconds' => null,
        ]);





    }
}
