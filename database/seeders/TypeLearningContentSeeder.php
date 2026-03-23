<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeLearningContent;

class TypeLearningContentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tipo: LINK (ID: 1)
        $typeLink = TypeLearningContent::create([
            'name' => 'link',
            'enabled' => true,
        ]);

        // Formato ID 1: youtube
        $typeLink->formats()->create([
            'name' => 'youtube',
            'max_size_bytes' => null,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => true,
        ]);

        // 2. Tipo: ARCHIVE (ID: 2)
        $typeArchive = TypeLearningContent::create([
            'name' => 'archive',
            'enabled' => true,
        ]);

        // Formatos ID 2 al 9 para Archive
        $archiveFormats = [
            'pdf',  // ID 2
            'mp4',  // ID 3
            'mp3',  // ID 4
            'docx', // ID 5
            'pptx', // ID 6
            'xlsx', // ID 7
            'zip',  // ID 8
            'txt',  // ID 9
        ];

        foreach ($archiveFormats as $formatName) {
            $isMedia = in_array($formatName, ['mp4', 'mp3']);

            $typeArchive->formats()->create([
                'name' => $formatName,
                'max_size_bytes' => 20 * 1024 * 1024, // Te lo bajé a 20MB para proteger tu Cloudinary 😉
                'min_duration_seconds' => $isMedia ? 120 : null,
                'max_duration_seconds' => $isMedia ? 480 : null,
                'enabled' => true,
            ]);
        }

        // --- NUEVOS FORMATOS PARA TIPO LINK ---

        // Formato ID 10: google_drive
        $typeLink->formats()->create([
            'name' => 'google_drive',
            'max_size_bytes' => null,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => true,
        ]);

        // Formato ID 11: onedrive
        $typeLink->formats()->create([
            'name' => 'onedrive',
            'max_size_bytes' => null,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled' => true,
        ]);
    }
}