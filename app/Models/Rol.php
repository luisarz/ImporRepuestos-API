<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Rol extends SpatieRole
{
    protected $table = 'roles';
    protected $fillable = ['name', 'guard_name', 'is_active', 'description'];

    public $timestamps = true;

    protected $casts = [
        'is_active' => 'integer',
    ];

    // Especificar guard por defecto
    protected $guard_name = 'api';

    /**
     * Obtener permisos agrupados por módulo
     * Útil para mostrar los permisos organizados en la UI
     */
    public function getPermissionsByModule()
    {
        return $this->permissions()
            ->with('module')
            ->get()
            ->groupBy(function($permission) {
                return $permission->module ? $permission->module->nombre : 'Sin módulo';
            });
    }

    /**
     * Obtener IDs de permisos como array
     * Útil para enviar al frontend
     */
    public function getPermissionIds()
    {
        return $this->permissions()->pluck('id')->toArray();
    }

    /**
     * Scope para filtrar por estado activo
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope para filtrar por estado inactivo
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }
}
