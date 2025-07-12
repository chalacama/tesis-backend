<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chapter;
class ChaptersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Chapter::create([
            'title' => 'Introducción a Laravel',
            'description' => 'Video introductorio sobre Laravel.',
            'module_id' => 1,
            'order' => 1,
        ]);
        Chapter::create([
            'title' => 'Introducción a Angular',
            'description' => 'Video introductorio sobre Angular.',
            'module_id' => 1,
            'order' => 2,
        ]);
    }
}
