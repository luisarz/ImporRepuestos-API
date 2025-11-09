<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * Guard name for Spatie Permission
     */
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'employee_id',
        'email_verifed_at',
        'password',
        'rememeber_tokend',
        'theme',
        'teheme_color',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'employee_id' => 'integer',
        'email_verifed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Obtener módulos permitidos para el usuario
     * Usado para cargar el sidebar con solo los módulos que el usuario puede acceder
     */
    public function getAllowedModules()
    {
        // Si el usuario es Super Admin, devolver todos los módulos activos
        if ($this->hasRole('Super Admin')) {
            return Modulo::where('is_active', 1)->orderBy('orden')->get();
        }

        // Obtener todos los permisos del usuario a través de sus roles
        $permissions = $this->getAllPermissions();

        // Extraer los module_ids únicos
        $moduleIds = $permissions->pluck('module_id')->unique()->filter();

        if ($moduleIds->isEmpty()) {
            return collect([]);
        }

        // Obtener módulos hijos (los que tienen permisos asignados)
        $childModules = Modulo::whereIn('id', $moduleIds)
            ->where('is_active', 1)
            ->get();

        // Obtener IDs de módulos padre
        $parentIds = $childModules->pluck('id_padre')->unique()->filter();

        // Obtener módulos padre
        $parentModules = Modulo::whereIn('id', $parentIds)
            ->where('is_active', 1)
            ->get();

        // Combinar módulos hijos y padres, eliminar duplicados y ordenar
        return $childModules->merge($parentModules)
            ->unique('id')
            ->sortBy('orden')
            ->values();
    }

    /**
     * Obtener permisos del usuario para un módulo específico
     * Útil para determinar qué botones/acciones mostrar en cada módulo
     */
    public function getPermissionsForModule($moduleId)
    {
        return $this->getAllPermissions()
            ->where('module_id', $moduleId)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Verificar si el usuario puede acceder a un módulo
     */
    public function canAccessModule($moduleId)
    {
        // Super Admin puede acceder a todo
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        return $this->getAllPermissions()
            ->where('module_id', $moduleId)
            ->isNotEmpty();
    }
}
