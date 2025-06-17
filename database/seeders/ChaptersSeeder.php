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
            'name' => 'Introducción a Laravel',
            'description' => 'Video introductorio sobre Laravel.',
            'modulo_id' => 1,
            'learning_content_id' => 1,
            'form_id' => 1,
            'order' => 1,
            'enabled' => true,
        ]);
        Chapter::create([
            'name' => 'Introducción a Angular',
            'description' => 'Video introductorio sobre Angular.',
            'modulo_id' => 1,
            'learning_content_id' => 2,
            'form_id' => null,
            'order' => 2,
            'enabled' => true,
        ]);
    }
}
