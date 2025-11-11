<?php
// Script para limpiar OPcache de PHP
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpiado exitosamente\n";
} else {
    echo "⚠️ OPcache no está habilitado\n";
}

// También limpiar el cache de realpath
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    echo "✅ StatCache limpiado exitosamente\n";
}

echo "\nPHP Version: " . phpversion() . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
