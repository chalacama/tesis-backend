<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\EloquentSortable\Sortable;
class CategoryCourse extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = ['course_id','category_id','order'];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => false,
    ];
}
