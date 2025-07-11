<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Sede;
use App\Models\Career;
class CareerSede extends Model
{
    protected $fillable = [
        'sede_id',
        'career_id',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function career()
    {
        return $this->belongsTo(Career::class);
    }
}
