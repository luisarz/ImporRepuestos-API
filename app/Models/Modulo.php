<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulo';
    public $timestamps = false;
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
        'is_active',
    ];
    public function permission(){
        return $this->hasMany(Permission::class,'module_id','id');
    }
}
