<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Solo agregar module_id si no existe
            if (!Schema::hasColumn('permissions', 'module_id')) {
                $table->unsignedBigInteger('module_id')->nullable()->after('guard_name');
            }

            // Agregar category y friendly_name
            if (!Schema::hasColumn('permissions', 'category')) {
                $table->string('category', 50)->default('crud');
            }

            if (!Schema::hasColumn('permissions', 'friendly_name')) {
                $table->string('friendly_name', 100)->nullable();
            }
        });

        // Limpiar permisos con module_id que no existen en modulo
        $db = Schema::getConnection();
        if (Schema::hasColumn('permissions', 'module_id')) {
            $db->statement("
                UPDATE permissions
                SET module_id = NULL
                WHERE module_id IS NOT NULL
                AND module_id NOT IN (SELECT id FROM modulo)
            ");
        }

        // Agregar foreign key si no existe
        $foreignKeyExists = $db->select("
            SELECT COUNT(*) as count
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'permissions'
            AND COLUMN_NAME = 'module_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if ($foreignKeyExists[0]->count == 0 && Schema::hasColumn('permissions', 'module_id')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->foreign('module_id')
                      ->references('id')
                      ->on('modulo')
                      ->onDelete('cascade');
            });
        }

        // Agregar índice si no existe
        $indexExists = $db->select("
            SELECT COUNT(*) as count
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'permissions'
            AND INDEX_NAME = 'permissions_module_id_category_index'
        ");

        if ($indexExists[0]->count == 0 && Schema::hasColumn('permissions', 'module_id') && Schema::hasColumn('permissions', 'category')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->index(['module_id', 'category']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropIndex(['module_id', 'category']);
            $table->dropColumn(['module_id', 'category', 'friendly_name']);
        });
    }
};
