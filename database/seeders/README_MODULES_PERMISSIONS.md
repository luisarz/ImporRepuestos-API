# 🔐 Sistema de Módulos y Permisos - ImporRepuestos

Este documento describe el sistema completo de módulos y permisos implementado para ImporRepuestos.

## 📋 Contenido

1. [Descripción General](#descripción-general)
2. [Estructura de Módulos](#estructura-de-módulos)
3. [Sistema de Permisos](#sistema-de-permisos)
4. [Roles Predefinidos](#roles-predefinidos)
5. [Ejecución de Seeders](#ejecución-de-seeders)
6. [Verificación](#verificación)

---

## 📖 Descripción General

El sistema implementa un control de acceso basado en roles (RBAC) utilizando Spatie Laravel Permission. Cada módulo del frontend tiene permisos asociados que controlan las acciones que los usuarios pueden realizar.

### Componentes Principales

- **Módulos**: 61 módulos organizados jerárquicamente (10 padres, 51 hijos)
- **Permisos**: Acciones específicas por módulo (view, create, edit, delete, etc.)
- **Roles**: 5 roles predefinidos con diferentes niveles de acceso

---

## 🗂️ Estructura de Módulos

### Módulos Padre (10)

| ID | Nombre | Hijos |
|----|--------|-------|
| 2 | Configuración | 6 |
| 10 | Catálogos | 4 |
| 20 | Catálogos Hacienda | 14 |
| 40 | Proveedores | 4 |
| 50 | Clientes | 2 |
| 70 | Productos | 5 |
| 80 | Inventario | 4 |
| 90 | Parque Vehicular | 5 |
| 100 | Compras | 2 |
| 110 | Ventas | 3 |
| 120 | Facturación Electrónica | 2 |

### Módulos Standalone (4)

| ID | Nombre | Ruta |
|----|--------|------|
| 1 | Dashboard | / |
| 60 | Empleados | /employees |
| 130 | Reportes | /reports |

### Total: 61 Módulos

---

## 🔑 Sistema de Permisos

### Acciones Estándar

Para la mayoría de los módulos se crean 4 permisos:

- `view_[modulo]` - Ver/Listar
- `create_[modulo]` - Crear nuevo
- `edit_[modulo]` - Editar existente
- `delete_[modulo]` - Eliminar

### Módulos con Permisos Especiales

#### Dashboard (Solo visualización)
- `view_dashboard`

#### Inventario (Con exportación)
- `view_inventario_general`
- `export_inventario_general`
- `view_inventarios_por_lote`
- `export_inventarios_por_lote`
- `view_kardex`
- `export_kardex`

#### Facturación Electrónica
- `view_historial_dtes`
- `download_historial_dtes`
- `resend_historial_dtes`
- `view_contingencias`
- `create_contingencias`
- `resolve_contingencias`

#### Nueva Venta (Sin edición/eliminación)
- `view_nueva_venta`
- `create_nueva_venta`

---

## 👥 Roles Predefinidos

### 1. Super Admin
- **Descripción**: Acceso total al sistema
- **Permisos**: TODOS (~200+ permisos)
- **Uso**: Administrador del sistema, desarrollo

### 2. Administrador
- **Descripción**: Gestión completa excepto configuraciones críticas
- **Permisos**: Todos excepto:
  - `delete_empresa`
  - `delete_usuarios`
  - `delete_roles`
  - `edit_configuración`
- **Uso**: Gerente general, administrador de operaciones

### 3. Vendedor
- **Descripción**: Gestión de ventas y clientes
- **Permisos**: 19 permisos específicos
- **Acceso a**:
  - Dashboard
  - Módulo completo de Ventas
  - Módulo completo de Clientes
  - Productos (solo visualización)
  - Inventario (solo visualización)
  - DTEs (solo visualización)

**Permisos del Vendedor**:
```
view_dashboard
view_ventas, view_lista_de_ventas, create_lista_de_ventas, edit_lista_de_ventas
view_nueva_venta, create_nueva_venta
view_detalles_de_pago
view_clientes, view_lista_de_clientes, create_lista_de_clientes, edit_lista_de_clientes
view_direcciones
view_productos, view_lista_de_productos
view_inventario, view_inventario_general
view_historial_dtes
```

### 4. Almacenista
- **Descripción**: Gestión de inventarios y almacenes
- **Permisos**: 21 permisos específicos
- **Acceso a**:
  - Dashboard
  - Módulo completo de Inventario (con exportación)
  - Productos (visualización y edición)
  - Lotes (gestión completa)
  - Compras (gestión completa)
  - Almacenes

**Permisos del Almacenista**:
```
view_dashboard
view_inventario, view_inventario_general
view_inventarios_por_lote, export_inventarios_por_lote
view_historial_de_costos, export_historial_de_costos
view_kardex, export_kardex
view_productos, view_lista_de_productos, edit_lista_de_productos
view_lotes, create_lotes, edit_lotes
view_almacenes
view_compras, create_compras, edit_compras
```

### 5. Contador
- **Descripción**: Acceso a reportes y facturación
- **Permisos**: 19 permisos específicos
- **Acceso a**:
  - Dashboard
  - Reportes completo
  - Facturación Electrónica (gestión de DTEs y contingencias)
  - Ventas (solo visualización)
  - Compras (solo visualización)
  - Clientes y Proveedores (solo visualización)

**Permisos del Contador**:
```
view_dashboard
view_reportes
view_facturación_electrónica
view_historial_dtes, download_historial_dtes, resend_historial_dtes
view_contingencias, create_contingencias, resolve_contingencias
view_ventas, view_lista_de_ventas
view_compras
view_clientes, view_lista_de_clientes
view_proveedores, view_lista_de_proveedores
```

---

## 🚀 Ejecución de Seeders

### Opción 1: Ejecutar Todos los Seeders (Recomendado)

```bash
# Desde el directorio raíz de la API
php artisan db:seed
```

Este comando ejecutará en orden:
1. `ModuloSeeder` - Crea los 61 módulos
2. `PermissionSeeder` - Crea ~200 permisos y 5 roles
3. Crea usuario de prueba con rol Super Admin

**Resultado esperado**:
```
🚀 Iniciando seeders del sistema ImporRepuestos...

📦 Ejecutando ModuloSeeder...
✅ 61 módulos creados exitosamente

🔐 Ejecutando PermissionSeeder...
✅ 200+ permisos creados exitosamente
✅ Rol Super Admin creado con 200+ permisos
✅ Rol Administrador creado con 195 permisos
✅ Rol Vendedor creado con 19 permisos
✅ Rol Almacenista creado con 21 permisos
✅ Rol Contador creado con 19 permisos

👤 Creando usuario de prueba...
✅ Usuario de prueba creado con rol Super Admin
   Email: test@example.com
   Password: password

🎉 ¡Todos los seeders se ejecutaron exitosamente!
```

### Opción 2: Ejecutar Seeders Individuales

```bash
# Solo módulos
php artisan db:seed --class=ModuloSeeder

# Solo permisos (requiere que los módulos existan)
php artisan db:seed --class=PermissionSeeder
```

### Opción 3: Ejecutar SQL Directamente

Si prefieres ejecutar SQL directamente en phpMyAdmin o MySQL:

```bash
# Ubicación del archivo
database/sql/insert_modulos.sql
```

**Nota**: Si usas el SQL, debes ejecutar el `PermissionSeeder` después:
```bash
php artisan db:seed --class=PermissionSeeder
```

### Resetear y Volver a Ejecutar

```bash
# PRECAUCIÓN: Esto borrará TODOS los datos
php artisan migrate:fresh --seed
```

---

## ✅ Verificación

### 1. Verificar Módulos

```bash
php artisan tinker
```

```php
// Contar módulos
\App\Models\Modulo::count(); // Debe ser 61

// Ver módulos padre
\App\Models\Modulo::where('is_padre', true)->get(['id', 'nombre']);

// Ver módulos de Ventas
\App\Models\Modulo::where('id_padre', 110)->get(['id', 'nombre', 'ruta']);
```

### 2. Verificar Permisos

```php
// Contar permisos
\Spatie\Permission\Models\Permission::count(); // ~200+

// Ver permisos de ventas
\Spatie\Permission\Models\Permission::where('name', 'like', '%venta%')->pluck('name');

// Ver permisos de un módulo específico
\Spatie\Permission\Models\Permission::where('module_id', 110)->pluck('name');
```

### 3. Verificar Roles

```php
// Listar roles
\Spatie\Permission\Models\Role::all(['name', 'description']);

// Ver permisos de un rol
$vendedor = \Spatie\Permission\Models\Role::findByName('Vendedor');
$vendedor->permissions->pluck('name');

// Contar permisos por rol
\Spatie\Permission\Models\Role::withCount('permissions')->get(['name', 'permissions_count']);
```

### 4. Verificar Usuario de Prueba

```php
// Obtener usuario
$user = \App\Models\User::where('email', 'test@example.com')->first();

// Ver roles
$user->roles->pluck('name');

// Ver permisos (a través del rol)
$user->getAllPermissions()->pluck('name');

// Verificar permiso específico
$user->can('view_ventas'); // Debe ser true
```

### 5. Consultas SQL de Verificación

```sql
-- Contar módulos por padre
SELECT
    COALESCE(p.nombre, 'Sin padre') as padre,
    COUNT(*) as total_hijos
FROM modulo m
LEFT JOIN modulo p ON m.id_padre = p.id
GROUP BY m.id_padre, p.nombre
ORDER BY total_hijos DESC;

-- Ver estructura jerárquica
SELECT
    p.nombre as padre,
    m.nombre as hijo,
    m.ruta
FROM modulo m
LEFT JOIN modulo p ON m.id_padre = p.id
ORDER BY p.orden, m.orden;

-- Permisos por módulo
SELECT
    m.nombre as modulo,
    COUNT(p.id) as total_permisos
FROM modulo m
LEFT JOIN permissions p ON m.id = p.module_id
GROUP BY m.id, m.nombre
ORDER BY total_permisos DESC;

-- Permisos por rol
SELECT
    r.name as rol,
    COUNT(rp.permission_id) as total_permisos
FROM roles r
LEFT JOIN role_has_permissions rp ON r.id = rp.role_id
GROUP BY r.id, r.name
ORDER BY total_permisos DESC;
```

---

## 🔧 Uso en el Código

### Middleware de Permisos

```php
// En routes/api.php
Route::middleware(['auth:api', 'permission:view_ventas'])->group(function () {
    Route::get('/sales', [SaleController::class, 'index']);
});

// Múltiples permisos (OR)
Route::middleware(['permission:view_ventas|create_ventas'])->group(function () {
    // ...
});
```

### En Controladores

```php
public function index()
{
    // Verificar permiso
    if (!auth()->user()->can('view_ventas')) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    // O usar abort
    abort_if(!auth()->user()->can('view_ventas'), 403);

    // Lógica del controlador
}
```

### Verificar Roles

```php
// Verificar rol
if (auth()->user()->hasRole('Super Admin')) {
    // ...
}

// Verificar múltiples roles
if (auth()->user()->hasAnyRole(['Super Admin', 'Administrador'])) {
    // ...
}
```

### En el Frontend (Vue.js)

Después de login, el frontend debe recibir:

```javascript
// Respuesta del login
{
    "user": {
        "id": 1,
        "name": "Usuario",
        "email": "user@example.com",
        "roles": ["Vendedor"],
        "permissions": [
            "view_dashboard",
            "view_ventas",
            "create_ventas",
            // ... más permisos
        ]
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

Usar en componentes:

```vue
<template>
    <button v-if="can('create_ventas')">
        Nueva Venta
    </button>
</template>

<script>
export default {
    computed: {
        can() {
            return (permission) => {
                return this.$store.getters['auth/permissions'].includes(permission);
            };
        }
    }
}
</script>
```

---

## 📝 Notas Importantes

1. **Orden de Ejecución**: Siempre ejecutar `ModuloSeeder` antes de `PermissionSeeder`
2. **Guard Name**: Todos los permisos usan `guard_name = 'api'` para JWT
3. **Cache de Permisos**: Spatie cachea permisos. Limpiar cache después de cambios:
   ```bash
   php artisan permission:cache-reset
   ```
4. **Normalización de Nombres**: Los nombres de permisos se normalizan (minúsculas, sin acentos, guiones bajos)
5. **Módulos Padre**: Los módulos padre solo tienen permiso `view`, los hijos tienen permisos completos

---

## 🔄 Actualización de Permisos

Si agregas nuevos módulos o cambias permisos:

```bash
# 1. Agregar módulos en ModuloSeeder
# 2. Actualizar lógica en PermissionSeeder si es necesario
# 3. Ejecutar seeders

php artisan db:seed --class=ModuloSeeder
php artisan db:seed --class=PermissionSeeder
php artisan permission:cache-reset
```

---

## 🆘 Solución de Problemas

### Error: "Class ModuloSeeder does not exist"
```bash
composer dump-autoload
```

### Error: "Table 'modulo' doesn't exist"
```bash
php artisan migrate
```

### Error: "SQLSTATE[23000]: Integrity constraint violation"
- Los módulos ya existen, ejecuta con `--force` o limpia la tabla primero

### Permisos no se reflejan
```bash
php artisan permission:cache-reset
```

---

## 📊 Resumen de Números

- **61 Módulos** (10 padres + 51 hijos)
- **~200 Permisos** (promedio de 3-4 por módulo)
- **5 Roles Predefinidos**
  - Super Admin: 200+ permisos
  - Administrador: 195 permisos
  - Vendedor: 19 permisos
  - Almacenista: 21 permisos
  - Contador: 19 permisos

---

## 🎯 Próximos Pasos

1. ✅ Ejecutar seeders
2. ✅ Verificar datos en base de datos
3. ⬜ Actualizar AuthController para incluir permisos en respuesta de login
4. ⬜ Implementar middleware de permisos en rutas API
5. ⬜ Crear composable en frontend para verificar permisos
6. ⬜ Implementar guards en router de Vue
7. ⬜ Crear UI para gestión de roles y permisos (Admin panel)

---

**Fecha de Creación**: 2025-11-10
**Versión**: 1.0
**Sistema**: ImporRepuestos
**Autor**: Claude Code
