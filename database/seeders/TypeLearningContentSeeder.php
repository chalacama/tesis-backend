<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeLearningContent;
use App\Models\Format;

class TypeLearningContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tipo: LINK
        $typeLink = TypeLearningContent::create([
            'name' => 'link',
            'enabled' => true,
        ]);

        // Formato para Link
        $typeLink->formats()->create([
            'name' => 'youtube',
            'max_size_bytes' => null,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => true,
        ]);

        // 2. Tipo: ARCHIVE
        $typeArchive = TypeLearningContent::create([
            'name' => 'archive',
            'enabled' => true,
        ]);

        // Formatos para Archive
        $archiveFormats = [
        'pdf', //2
        'mp4', //3
        'mp3', //4
        'docx', //5
        'pptx', //6
        'xlsx', //7
        'zip', //8
        'rar' , //9
        'txt', //10
        'jpg' , //11
        'png', //12
        'jpeg', //13
        'gif']; //14

        foreach ($archiveFormats as $formatName) {
            // Solo mp4 y mp3 tendrían límites de duración lógicos
            $isMedia = in_array($formatName, ['mp4', 'mp3']);

            $typeArchive->formats()->create([
                'name' => $formatName,
                'max_size_bytes' => 800.001 * 1024 * 1024,
                'min_duration_seconds' => $isMedia ? 120 : null,
                'max_duration_seconds' => $isMedia ? 480 : null,
                'enabled' => true,
            ]);
        }
    }
}