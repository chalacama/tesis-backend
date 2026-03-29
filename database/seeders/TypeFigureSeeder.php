<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeFigureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'link.image', 'max_size_bytes' => null, 'enabled' => true],
            ['name' => 'archive.image', 'max_size_bytes' => 300000, 'enabled' => true],
        ];

        foreach ($types as $type) {
            \App\Models\TypeFigure::create($type);
        }
    }
}
