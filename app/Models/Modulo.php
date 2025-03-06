<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulo';
    use HasFactory;


    protected $fillable = [
        'id',
        'nombre',
        'icono',
        'ruta',
        'id_padre',
        'is_padre',
        'orden',
        'is_minimazed',
        'target',
        'is_active'
    ];
}
