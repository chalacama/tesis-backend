<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class TypeFigure extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'name',
        'max_size_bytes',
        'enabled'
    ];
}
