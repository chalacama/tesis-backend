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
            ['name' => 'link.image', 'max_size_bytes' => null, 'width' => null, 'height' => null, 'aspect_ratio' => null, 'enabled' => true],
            ['name' => 'archive.image', 'max_size_bytes' => 4000000, 'width' => null, 'height' => null, 'aspect_ratio' => null, 'enabled' => true],
        ];

        foreach ($types as $type) {
            \App\Models\TypeThumbnail::create($type);
        }
    }
}
