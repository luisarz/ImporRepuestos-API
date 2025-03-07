<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleRol extends Model
{
    public $timestamps = false;
    protected $table = 'modulo_rol';
     protected $fillable = [
        'id_module',
        'id_rol',
        'is_active'
    ];
}
