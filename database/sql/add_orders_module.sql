-- =====================================================
-- Script para agregar módulo de Órdenes
-- =====================================================
-- Este script crea el módulo padre "Órdenes" y sus submódulos
-- con todos los permisos necesarios
-- =====================================================

-- 1. Crear módulo padre "Órdenes"
INSERT INTO `modulo` (`nombre`, `icono`, `ruta`, `id_padre`, `is_padre`, `orden`, `is_minimazed`, `target`, `is_active`, `created_at`, `updated_at`)
VALUES ('Órdenes', 'ki-filled ki-clipboard', '#', NULL, 1, 13, 0, '_self', 1, NOW(), NOW());

-- Obtener el ID del módulo padre recién creado
SET @orders_parent_id = LAST_INSERT_ID();

-- 2. Crear submódulo "Lista de Órdenes"
INSERT INTO `modulo` (`nombre`, `icono`, `ruta`, `id_padre`, `is_padre`, `orden`, `is_minimazed`, `target`, `is_active`, `created_at`, `updated_at`)
VALUES ('Lista de Órdenes', 'ki-filled ki-document', '/orders', @orders_parent_id, 0, 1, 1, '_self', 1, NOW(), NOW());

-- Obtener el ID del submódulo
SET @orders_list_id = LAST_INSERT_ID();

-- 3. Crear submódulo "Nueva Orden (POS)"
INSERT INTO `modulo` (`nombre`, `icono`, `ruta`, `id_padre`, `is_padre`, `orden`, `is_minimazed`, `target`, `is_active`, `created_at`, `updated_at`)
VALUES ('Nueva Orden', 'ki-filled ki-add-files', '/orders/add', @orders_parent_id, 0, 2, 1, '_self', 1, NOW(), NOW());

-- Obtener el ID del submódulo POS
SET @orders_new_id = LAST_INSERT_ID();

-- =====================================================
-- 4. Crear permisos para "Lista de Órdenes"
-- =====================================================

-- Permisos CRUD básicos
INSERT INTO `permissions` (`name`, `guard_name`, `module_id`, `category`, `friendly_name`, `created_at`, `updated_at`)
VALUES
    ('lista_de_ordenes.view', 'api', @orders_list_id, 'transaction', 'Ver', NOW(), NOW()),
    ('lista_de_ordenes.create', 'api', @orders_list_id, 'transaction', 'Crear', NOW(), NOW()),
    ('lista_de_ordenes.update', 'api', @orders_list_id, 'transaction', 'Editar', NOW(), NOW()),
    ('lista_de_ordenes.cancel', 'api', @orders_list_id, 'transaction', 'Anular', NOW(), NOW()),
    ('lista_de_ordenes.delete', 'api', @orders_list_id, 'transaction', 'Eliminar', NOW(), NOW()),
    ('lista_de_ordenes.export', 'api', @orders_list_id, 'transaction', 'Exportar', NOW(), NOW());

-- Permisos específicos de órdenes
INSERT INTO `permissions` (`name`, `guard_name`, `module_id`, `category`, `friendly_name`, `created_at`, `updated_at`)
VALUES
    ('ordenes.print_pdf', 'api', @orders_list_id, 'transaction', 'Imprimir PDF', NOW(), NOW()),
    ('ordenes.print_ticket', 'api', @orders_list_id, 'transaction', 'Imprimir Ticket', NOW(), NOW()),
    ('ordenes.convert_to_sale', 'api', @orders_list_id, 'transaction', 'Convertir a Venta', NOW(), NOW()),
    ('ordenes.view_items', 'api', @orders_list_id, 'transaction', 'Ver Items', NOW(), NOW());

-- =====================================================
-- 5. Crear permisos para "Nueva Orden (POS)"
-- =====================================================

INSERT INTO `permissions` (`name`, `guard_name`, `module_id`, `category`, `friendly_name`, `created_at`, `updated_at`)
VALUES
    ('nueva_orden.access', 'api', @orders_new_id, 'transaction', 'Acceder al POS', NOW(), NOW()),
    ('nueva_orden.add_items', 'api', @orders_new_id, 'transaction', 'Agregar Items', NOW(), NOW()),
    ('nueva_orden.edit_items', 'api', @orders_new_id, 'transaction', 'Editar Items', NOW(), NOW()),
    ('nueva_orden.remove_items', 'api', @orders_new_id, 'transaction', 'Eliminar Items', NOW(), NOW()),
    ('nueva_orden.apply_discount', 'api', @orders_new_id, 'transaction', 'Aplicar Descuento', NOW(), NOW()),
    ('nueva_orden.save', 'api', @orders_new_id, 'transaction', 'Guardar Orden', NOW(), NOW());

-- =====================================================
-- 6. Asignar permisos al rol de Administrador (id=1)
-- =====================================================
-- Esto asume que el rol Administrator tiene id=1
-- Ajusta el id si es diferente en tu sistema

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT `id`, 1 FROM `permissions`
WHERE `module_id` IN (@orders_list_id, @orders_new_id);

-- =====================================================
-- Verificación
-- =====================================================
-- Ejecuta estas queries para verificar que todo se creó correctamente:

-- SELECT * FROM modulo WHERE nombre LIKE '%Orden%';
-- SELECT * FROM permissions WHERE module_id IN (SELECT id FROM modulo WHERE nombre LIKE '%Orden%');
-- SELECT COUNT(*) as total_permisos FROM permissions WHERE module_id IN (SELECT id FROM modulo WHERE nombre LIKE '%Orden%');

-- =====================================================
-- NOTAS:
-- =====================================================
-- 1. Este script crea 3 módulos: 1 padre y 2 hijos
-- 2. Se crean 16 permisos en total
-- 3. Los permisos se asignan automáticamente al rol Administrator
-- 4. Si necesitas asignar a otros roles, hazlo manualmente o modifica el script
-- 5. Los iconos usan Keenicons (ki-filled ki-*)
-- =====================================================
