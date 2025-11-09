<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name', 'module_id', 'category', 'friendly_name'];

    protected $appends = ['display_name'];

    /**
     * Relación con el módulo
     */
    public function module()
    {
        return $this->belongsTo(Modulo::class, 'module_id');
    }

    /**
     * Scope para filtrar por módulo
     */
    public function scopeOfModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Obtener nombre amigable del permiso
     */
    public function getDisplayNameAttribute()
    {
        // Si tiene friendly_name, usarlo
        if ($this->friendly_name) {
            return $this->friendly_name;
        }

        // Mapeo de acciones a nombres en español
        $actions = [
            'view' => 'Ver',
            'view_any' => 'Ver Todos',
            'create' => 'Crear',
            'update' => 'Editar',
            'delete' => 'Eliminar',
            'delete_any' => 'Eliminar Varios',
            'export' => 'Exportar',
            'import' => 'Importar',
            'authorize' => 'Autorizar',
            'cancel' => 'Anular',
            'generate' => 'Generar',
            'manage_permissions' => 'Gestionar Permisos',
            'restore' => 'Restaurar',
            'restore_any' => 'Restaurar Varios',
            'replicate' => 'Replicar',
            'reorder' => 'Reordenar',
            'force_delete' => 'Eliminar Permanentemente',
            'force_delete_any' => 'Eliminar Permanentemente Varios',
            'bulk_activate' => 'Activar en Lote',
            'bulk_deactivate' => 'Desactivar en Lote',
            'bulk_delete' => 'Eliminar en Lote',
            'bulk_export' => 'Exportar en Lote',
        ];

        // Extraer la acción del nombre del permiso (formato: module.action)
        $parts = explode('.', $this->name);
        $action = end($parts);

        return $actions[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }
}
