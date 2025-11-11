-- =====================================================
-- SCRIPT DE INSERCIÓN DE MÓDULOS COMPLETOS
-- ImporRepuestos - Sistema de Gestión de Repuestos
-- =====================================================

-- Limpiar tabla (opcional, comentar si no deseas limpiar)
-- TRUNCATE TABLE modulo;

-- ========== DASHBOARD ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(1, 'Dashboard', 'ki-filled ki-home', '/', NULL, 0, 1, 0, '_self', 1);

-- ========== CONFIGURACIÓN ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(2, 'Configuración', 'ki-filled ki-setting-2', '#', NULL, 1, 2, 0, '_self', 1),
(3, 'Empresa', 'ki-filled ki-shop', '/company', 2, 0, 1, 0, '_self', 1),
(4, 'Almacenes', 'ki-filled ki-home', '/warehouse', 2, 0, 2, 0, '_self', 1),
(5, 'Módulos', 'ki-filled ki-category', '/module', 2, 0, 3, 0, '_self', 1),
(6, 'Roles', 'ki-filled ki-security-user', '/setting/rol', 2, 0, 4, 0, '_self', 1),
(7, 'Usuarios', 'ki-filled ki-profile-user', '/setting/users', 2, 0, 5, 0, '_self', 1);

-- ========== CATÁLOGOS GENERALES ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(10, 'Catálogos', 'ki-filled ki-book', '#', NULL, 1, 3, 0, '_self', 1),
(11, 'Grupos de Categorías', 'ki-filled ki-category', '/category-groups', 10, 0, 1, 0, '_self', 1),
(12, 'Subcategorías', 'ki-filled ki-subtitle', '/sub-category', 10, 0, 2, 0, '_self', 1),
(13, 'Marcas', 'ki-filled ki-tag', '/brands', 10, 0, 3, 0, '_self', 1),
(14, 'Cargos', 'ki-filled ki-briefcase', '/jobtitles', 10, 0, 4, 0, '_self', 1);

-- ========== CATÁLOGOS HACIENDA ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(20, 'Catálogos Hacienda', 'ki-filled ki-bank', '#', NULL, 1, 4, 0, '_self', 1),
(21, 'Condiciones de Operación', 'ki-filled ki-check-circle', '/operation-condition', 20, 0, 1, 0, '_self', 1),
(22, 'Métodos de Pago', 'ki-filled ki-credit-card', '/payment-methods', 20, 0, 2, 0, '_self', 1),
(23, 'Documentos Tributarios', 'ki-filled ki-file', '/cat-doc-tributario', 20, 0, 3, 0, '_self', 1),
(24, 'Actividades Económicas', 'ki-filled ki-chart-line-up', '/economic-activities', 20, 0, 4, 0, '_self', 1),
(25, 'Unidades de Medida', 'ki-filled ki-chart-simple', '/unit-measurements', 20, 0, 5, 0, '_self', 1),
(26, 'Ambiente Destino', 'ki-filled ki-setting', '/cat-ambiente-destino', 20, 0, 6, 0, '_self', 1),
(27, 'Países', 'ki-filled ki-flag', '/countries', 20, 0, 7, 0, '_self', 1),
(28, 'Departamentos', 'ki-filled ki-map', '/departments', 20, 0, 8, 0, '_self', 1),
(29, 'Municipios', 'ki-filled ki-geolocation', '/municipalities', 20, 0, 9, 0, '_self', 1),
(30, 'Distritos', 'ki-filled ki-location', '/districts', 20, 0, 10, 0, '_self', 1),
(31, 'Tipos de Documento Cliente', 'ki-filled ki-badge', '/cat-customer-doc-types', 20, 0, 11, 0, '_self', 1),
(32, 'Tipos de Cliente', 'ki-filled ki-people', '/customer-types', 20, 0, 12, 0, '_self', 1),
(33, 'Tipos de Establecimiento', 'ki-filled ki-home-2', '/stablishment-types', 20, 0, 13, 0, '_self', 1),
(34, 'Tipos de Contingencia', 'ki-filled ki-shield-cross', '/contingency-types', 20, 0, 14, 0, '_self', 1);

-- ========== PROVEEDORES ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(40, 'Proveedores', 'ki-filled ki-shop', '#', NULL, 1, 5, 0, '_self', 1),
(41, 'Tipos de Proveedor', 'ki-filled ki-category', '/providers-types', 40, 0, 1, 0, '_self', 1),
(42, 'Tipos de Documento', 'ki-filled ki-document', '/providers-documents-types', 40, 0, 2, 0, '_self', 1),
(43, 'Lista de Proveedores', 'ki-filled ki-profile-user', '/providers', 40, 0, 3, 0, '_self', 1),
(44, 'Direcciones', 'ki-filled ki-geolocation', '/provider-addresses', 40, 0, 4, 0, '_self', 1);

-- ========== CLIENTES ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(50, 'Clientes', 'ki-filled ki-people', '#', NULL, 1, 6, 0, '_self', 1),
(51, 'Lista de Clientes', 'ki-filled ki-profile-user', '/customers', 50, 0, 1, 0, '_self', 1),
(52, 'Direcciones', 'ki-filled ki-geolocation', '/customer-addresses', 50, 0, 2, 0, '_self', 1);

-- ========== EMPLEADOS ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(60, 'Empleados', 'ki-filled ki-user', '/employees', NULL, 0, 7, 0, '_self', 1);

-- ========== PRODUCTOS ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(70, 'Productos', 'ki-filled ki-package', '#', NULL, 1, 8, 0, '_self', 1),
(71, 'Lista de Productos', 'ki-filled ki-category', '/products', 70, 0, 1, 0, '_self', 1),
(72, 'Equivalencias', 'ki-filled ki-arrows-loop', '/equivalents', 70, 0, 2, 0, '_self', 1),
(73, 'Intercambios', 'ki-filled ki-shuffle', '/interchanges', 70, 0, 3, 0, '_self', 1),
(74, 'Lotes', 'ki-filled ki-calendar', '/lotes', 70, 0, 4, 0, '_self', 1),
(75, 'Orígenes de Código de Lote', 'ki-filled ki-code', '/batch-code-origins', 70, 0, 5, 0, '_self', 1);

-- ========== INVENTARIO ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(80, 'Inventario', 'ki-filled ki-cube', '#', NULL, 1, 9, 0, '_self', 1),
(81, 'Inventario General', 'ki-filled ki-chart-simple-2', '/inventory', 80, 0, 1, 0, '_self', 1),
(82, 'Inventarios por Lote', 'ki-filled ki-calendar-tick', '/inventory/batches', 80, 0, 2, 0, '_self', 1),
(83, 'Historial de Costos', 'ki-filled ki-chart-line-down', '/cost-history', 80, 0, 3, 0, '_self', 1),
(84, 'Kardex', 'ki-filled ki-note-2', '/kardex', 80, 0, 4, 0, '_self', 1);

-- ========== PARQUE VEHICULAR ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(90, 'Parque Vehicular', 'ki-filled ki-truck', '#', NULL, 1, 10, 0, '_self', 1),
(91, 'Tipos de Placa', 'ki-filled ki-tablet', '/plate-types', 90, 0, 1, 0, '_self', 1),
(92, 'Modelos de Vehículos', 'ki-filled ki-car', '/vehicle-models', 90, 0, 2, 0, '_self', 1),
(93, 'Tipos de Combustible', 'ki-filled ki-flash', '/fuel-types', 90, 0, 3, 0, '_self', 1),
(94, 'Vehículos', 'ki-filled ki-truck', '/vehicles', 90, 0, 4, 0, '_self', 1),
(95, 'Aplicaciones', 'ki-filled ki-setting-2', '/applications', 90, 0, 5, 0, '_self', 1);

-- ========== COMPRAS ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(100, 'Compras', 'ki-filled ki-basket', '#', NULL, 1, 11, 0, '_self', 1),
(101, 'Compras', 'ki-filled ki-shop', '/purchases', 100, 0, 1, 0, '_self', 1),
(102, 'Cotizaciones', 'ki-filled ki-questionnaire-tablet', '/quote-purchases', 100, 0, 2, 0, '_self', 1);

-- ========== VENTAS ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(110, 'Ventas', 'ki-filled ki-dollar', '#', NULL, 1, 12, 0, '_self', 1),
(111, 'Lista de Ventas', 'ki-filled ki-file', '/sales', 110, 0, 1, 0, '_self', 1),
(112, 'Nueva Venta', 'ki-filled ki-add-files', '/sales/add', 110, 0, 2, 0, '_self', 1),
(113, 'Detalles de Pago', 'ki-filled ki-credit-card', '/sales/payments', 110, 0, 3, 0, '_self', 1);

-- ========== FACTURACIÓN ELECTRÓNICA ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(120, 'Facturación Electrónica', 'ki-filled ki-document', '#', NULL, 1, 13, 0, '_self', 1),
(121, 'Historial de DTEs', 'ki-filled ki-file-sheet', '/dte/history', 120, 0, 1, 0, '_self', 1),
(122, 'Contingencias', 'ki-filled ki-shield-cross', '/contingencies', 120, 0, 2, 0, '_self', 1);

-- ========== REPORTES ==========
INSERT INTO modulo (id, nombre, icono, ruta, id_padre, is_padre, orden, is_minimazed, target, is_active) VALUES
(130, 'Reportes', 'ki-filled ki-chart-simple', '/reports', NULL, 0, 14, 0, '_self', 1);

-- =====================================================
-- RESUMEN: 61 módulos registrados
-- =====================================================
-- Módulos padre (con submenús): 10
-- Módulos hijo (opciones): 51
-- =====================================================
