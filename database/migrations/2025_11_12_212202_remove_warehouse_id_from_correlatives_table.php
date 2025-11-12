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
        Schema::table('correlatives', function (Blueprint $table) {
            // Eliminar unique constraint que incluye warehouse_id
            $table->dropUnique('unique_active_correlative_per_doc_type');
        });

        Schema::table('correlatives', function (Blueprint $table) {
            // Eliminar columna warehouse_id
            $table->dropColumn('warehouse_id');
        });

        Schema::table('correlatives', function (Blueprint $table) {
            // Recrear unique constraint usando cash_register_id en lugar de warehouse_id
            $table->unique(['cash_register_id', 'document_type_id', 'is_active'], 'unique_active_correlative_per_doc_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correlatives', function (Blueprint $table) {
            // Eliminar el unique constraint actual
            $table->dropUnique('unique_active_correlative_per_doc_type');
        });

        Schema::table('correlatives', function (Blueprint $table) {
            // Recrear columna warehouse_id
            $table->unsignedBigInteger('warehouse_id')->after('id');
        });

        Schema::table('correlatives', function (Blueprint $table) {
            // Recrear unique constraint con warehouse_id
            $table->unique(['warehouse_id', 'document_type_id', 'is_active'], 'unique_active_correlative_per_doc_type');
        });
    }
};
