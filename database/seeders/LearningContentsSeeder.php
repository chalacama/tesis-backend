<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LearningContent;
class LearningContentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 'pdf', //1
        // 'mp4', //2
        // 'mp3', //3
        // 'docx', //4
        // 'pptx', //5
        // 'xlsx', //6
        // 'zip', //7
        // 'rar' , //8
        // 'txt', //9
        // 'jpg' , //10
        // 'png', //11
        // 'jpeg', //12
        // 'gif', //13
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/video/upload/v1764845297/archives/chapter/1.mp4',
            'type_content_id' => 2,
            'chapter_id' => 1,
            'format_id' => 3, // mp4
            'size_bytes' => 500.00 * 1024 * 1024,
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
            'size_bytes' => 3.84 * 1024 * 1024,
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1763338367/archives/chapter/4.tmp',
            'type_content_id' => 2,
            'chapter_id' => 6,
            'format_id' => 5, // word
            'size_bytes' => 0.0366 * 1024 * 1024,
            'duration_seconds' => null,
        ]);

        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/image/upload/v1773456447/image_2e40c2_le5t6k.png',
            'type_content_id' => 2,
            'chapter_id' => 7,
            'format_id' => 11, // png
            'size_bytes' => 0.0366 * 1024 * 1024,
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773458582/gravestone-forge-1.20.1-1.0.35_dh9jzk.zip',
            'type_content_id' => 2,
            'chapter_id' => 8,
            'format_id' => 7, // zip
            'size_bytes' => 0.0366 * 1024 * 1024,
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773458690/Tabla_de_correciones_tesis_h8bzx2.xlsx',
            'type_content_id' => 2,
            'chapter_id' => 9,
            'format_id' => 6, // excel
            'size_bytes' => 0.0366 * 1024 * 1024,
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773475782/ARTICULO_129_-_CHALA_1_lhqwv2.docx',
            'type_content_id' => 2,
            'chapter_id' => 10,
            'format_id' => 4, // docx
            'size_bytes' => 0.0366 * 1024 * 1024,
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/raw/upload/v1773476522/FORMATO_PRESENTACIO%CC%81N_TIC_tpg6mu.pptx',
            'type_content_id' => 2,
            'chapter_id' => 11,
            'format_id' => 5, // pptx
            'size_bytes' => 0.0366 * 1024 * 1024,
            'duration_seconds' => null,
        ]);
        LearningContent::create([
            'url' => 'https://res.cloudinary.com/dvvqko1vv/video/upload/v1773476999/ssstik.io_1768127717922_e2j49d.mp3',
            'type_content_id' => 2,
            'chapter_id' => 12,
            'format_id' => 3, // mp3
            'size_bytes' => 0.0366 * 1024 * 1024,
            'duration_seconds' => null,
        ]);
    }
}
