<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeLearningContent;

/**
 * Mapa de IDs de formato (auto-increment, orden de inserción):
 *
 * ── LINK (type_id: 1) ─────────────────────────────────────────────────────
 *   1  → youtube
 *  10  → googledrive.mp4
 *  11  → googledrive.mp3
 *  12  → googledrive.pdf
 *  13  → googledrive.docx
 *  14  → googledrive.pptx
 *  15  → googledrive.xlsx
 *  16  → googledrive.zip
 *  17  → googledrive.txt
 *  18  → onedrive.mp4
 *  19  → onedrive.mp3
 *  20  → onedrive.pdf
 *  21  → onedrive.docx
 *  22  → onedrive.pptx
 *  23  → onedrive.xlsx
 *  24  → onedrive.zip
 *  25  → onedrive.txt
 *
 * ── ARCHIVE (type_id: 2) ──────────────────────────────────────────────────
 *   2  → pdf
 *   3  → mp4
 *   4  → mp3
 *   5  → docx
 *   6  → pptx
 *   7  → xlsx
 *   8  → zip
 *   9  → txt
 */
class TypeLearningContentSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tipo 1: LINK ───────────────────────────────────────────────────
        $typeLink = TypeLearningContent::create([
            'name'    => 'link',
            'enabled' => true,
        ]);

        // ID 1 → youtube
        $typeLink->formats()->create([
            'name'                 => 'youtube',
            'max_size_bytes'       => null,
            'min_duration_seconds' => null,
            'max_duration_seconds' => null,
            'enabled'              => true,
        ]);

        // ── Tipo 2: ARCHIVE ────────────────────────────────────────────────
        $typeArchive = TypeLearningContent::create([
            'name'    => 'archive',
            'enabled' => true,
        ]);

        // IDs 2 → 9 (archive formats)
        // Los formatos de media tienen límites de duración como ejemplo,
        // ajusta según tus reglas de negocio.
        $archiveFormats = [
            // name    min_sec  max_sec
            ['pdf',   null,    null ],  // ID 2
            ['mp4',   10,      480  ],  // ID 3
            ['mp3',   10,      480  ],  // ID 4
            ['docx',  null,    null ],  // ID 5
            ['pptx',  null,    null ],  // ID 6
            ['xlsx',  null,    null ],  // ID 7
            ['zip',   null,    null ],  // ID 8
            ['txt',   null,    null ],  // ID 9
        ];

        foreach ($archiveFormats as [$name, $minSec, $maxSec]) {
            $typeArchive->formats()->create([
                'name'                 => $name,
                'max_size_bytes'       => 800 * 1024 * 1024, // 800 MB
                'min_duration_seconds' => $minSec,
                'max_duration_seconds' => $maxSec,
                'enabled'              => true,
            ]);
        }

        // ── Formatos LINK adicionales (IDs 10 → 25) ───────────────────────
        // Google Drive  (IDs 10 – 17)
        $googleDriveFormats = [
            'googledrive.mp4',   // ID 10
            'googledrive.mp3',   // ID 11
            'googledrive.pdf',   // ID 12
            'googledrive.docx',  // ID 13
            'googledrive.pptx',  // ID 14
            'googledrive.xlsx',  // ID 15
            'googledrive.zip',   // ID 16
            'googledrive.txt',   // ID 17
        ];

        foreach ($googleDriveFormats as $formatName) {
            $typeLink->formats()->create([
                'name'                 => $formatName,
                'max_size_bytes'       => null,
                'min_duration_seconds' => null,
                'max_duration_seconds' => null,
                'enabled'              => true,
            ]);
        }

        // OneDrive (IDs 18 – 25)
        $onedriveFormats = [
            'onedrive.mp4',   // ID 18
            'onedrive.mp3',   // ID 19
            'onedrive.pdf',   // ID 20
            'onedrive.docx',  // ID 21
            'onedrive.pptx',  // ID 22
            'onedrive.xlsx',  // ID 23
            'onedrive.zip',   // ID 24
            'onedrive.txt',   // ID 25
        ];

        foreach ($onedriveFormats as $formatName) {
            $typeLink->formats()->create([
                'name'                 => $formatName,
                'max_size_bytes'       => null,
                'min_duration_seconds' => null,
                'max_duration_seconds' => null,
                'enabled'              => true,
            ]);
        }
    }
}