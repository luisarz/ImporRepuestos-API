<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    protected $table = 'category_groups';
    protected $fillable = [
        'code',
        'name',
        'active'
    ];
}
