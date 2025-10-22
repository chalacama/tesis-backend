<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LikeComment;
use App\Models\Comment;
use App\Models\User;
class LikeCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LikeComment::create([
            'user_id' => 1,
            'comment_id' => 1,
        ]);
        LikeComment::create([
            'user_id' => 2,
            'comment_id' => 1,
        ]);
        LikeComment::create([
            'user_id' => 1,
            'comment_id' => 3,
        ]);

        LikeComment::create([
            'user_id' => 3,
            'comment_id' => 2,
        ]);

        
        
    }
}
