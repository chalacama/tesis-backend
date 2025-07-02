<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AcademicProgram;
use App\Models\Career;
class AcademicCareer extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_program_id',
        'career_id',
    ];

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function career()
    {
        return $this->belongsTo(Career::class);
    }
}
