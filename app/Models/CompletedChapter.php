<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompletedChapter extends Model
{
    protected $table = 'completed_chapters';

    protected $fillable = [
        'chapter_id',
        'user_id',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
