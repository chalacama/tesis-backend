<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Test extends Model
{
    protected $table = 'tests';

    protected $fillable = [
        'chapter_id',
        'random',
        'incorrect',
        'score',
        'split',
        'limited',
        
    ];

    
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

}
