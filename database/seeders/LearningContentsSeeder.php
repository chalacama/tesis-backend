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
        // 'rar' , //9
        // 'txt', //10
        // 'jpg' , //11
        // 'png', //12
        // 'jpeg', //13
        // 'gif', //14
        LearningContent::create([
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
            'url' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1764846842/archives/chapter/3.pdf',
            'type_content_id' => 2,
            'chapter_id' => 3,
            'format_id' => 2, // pdf
            'size_bytes' => 4035040, //4.035.040 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1763338367/archives/chapter/4.tmp',
            'type_content_id' => 2,
            'chapter_id' => 12,
            'format_id' => 5, // word
            'size_bytes' => 2574 , //2.574 bytes
            'duration_seconds' => null,
        ]);

        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1773456447/image_2e40c2_le5t6k.png',
            'type_content_id' => 2,
            'chapter_id' => 6,
            'format_id' => 12, // png
            'size_bytes' => 205548, //205.548 bytes
            'duration_seconds' => null,
        ]);
        // ✅ CORRECTO: chapter 7 = word → docx
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773475782/ARTICULO_129_-_CHALA_1_lhqwv2.docx',
            'type_content_id' => 2,
            'chapter_id' => 7,   // Archivo editable word
            'format_id' => 5,    // docx
            'size_bytes' => 87856, //87.856 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773476522/FORMATO_PRESENTACIO%CC%81N_TIC_tpg6mu.pptx',
            'type_content_id' => 2,
            'chapter_id' => 8,   // Archivo editable pptx
            'format_id' => 6,    // pptx
            'size_bytes' => 78140, //78.140 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773458690/Tabla_de_correciones_tesis_h8bzx2.xlsx',
            'type_content_id' => 2,
            'chapter_id' => 9,   // Archivo editable excel
            'format_id' => 7,    // xlsx
            'size_bytes' => 45906, //45.906 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773458582/gravestone-forge-1.20.1-1.0.35_dh9jzk.zip',
            'type_content_id' => 2,
            'chapter_id' => 10,  // Archivo comprimido
            'format_id' => 8,    // zip
            'size_bytes' => 289748, //289.748 bytes
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/video/upload/v1773476999/ssstik.io_1768127717922_e2j49d.mp3',
            'type_content_id' => 2,
            'chapter_id' => 11,
            'format_id' => 4, // mp3
            'size_bytes' => 535450, //535.450 bytes
            'duration_seconds' => null,
        ]);
    }
}
