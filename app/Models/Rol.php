<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Rol extends Model
{
   protected $table = 'roles';
    protected $fillable = ['name', 'guard_name', 'is_active'];

    protected $casts = [
        'is_active' => 'integer',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    // Scope para filtrar por estado activo
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Scope para filtrar por estado inactivo
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }
}
