<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeThumbnailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'link.image', 'max_size_bytes' => null, 'width' => 300, 'height' => 200, 'aspect_ratio' => null, 'enabled' => true],
            ['name' => 'archive.image', 'max_size_bytes' => null, 'width' => 1200, 'height' => 400, 'aspect_ratio' => '16:9', 'enabled' => true],
        ];

        foreach ($types as $type) {
            \App\Models\TypeThumbnail::create($type);
        }
    }
}
