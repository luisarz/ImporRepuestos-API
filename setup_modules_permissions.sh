#!/bin/bash

echo "============================================"
echo "  ImporRepuestos - Setup Modules y Permisos"
echo "============================================"
echo ""

echo "[1/4] Verificando migraciones..."
php artisan migrate:status

echo ""
echo "[2/4] Ejecutando migraciones pendientes..."
php artisan migrate

echo ""
echo "[3/4] Ejecutando seeders (Modulos y Permisos)..."
php artisan db:seed

echo ""
echo "[4/4] Limpiando cache de permisos..."
php artisan permission:cache-reset

echo ""
echo "============================================"
echo "  Setup completado exitosamente!"
echo "============================================"
echo ""
echo "Credenciales de prueba:"
echo "  Email: test@example.com"
echo "  Password: password"
echo "  Rol: Super Admin"
echo ""
echo "Para verificar, ejecuta:"
echo "  php artisan tinker"
echo "  \\App\\Models\\Modulo::count()"
echo "  \\Spatie\\Permission\\Models\\Permission::count()"
echo "  \\Spatie\\Permission\\Models\\Role::count()"
echo ""
