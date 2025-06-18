<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReplyComment;
class ReplyCommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReplyComment::create([
            'texto' => '¡Totalmente de acuerdo!',
            'enabled' => true,
            'user_id' => 3, // Asegúrate de que el usuario y el comentario existan
            'comment_id' => 1,
        ]);
        ReplyComment::create([
            'texto' => 'Gracias por tu comentario.',
            'enabled' => true,
            'user_id' => 4,
            'comment_id' => 2,
        ]);
    }
}
