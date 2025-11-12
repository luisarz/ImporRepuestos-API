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
            // Eliminar índice y restricción unique antes de eliminar la columna
            $table->dropUnique('unique_active_correlative');
            $table->dropIndex(['document_type']);

            // Eliminar la columna document_type antigua
            $table->dropColumn('document_type');

            // Agregar la nueva columna document_type_id como FK
            $table->unsignedBigInteger('document_type_id')->after('warehouse_id')->comment('Tipo de documento DTE');

            // Agregar foreign key
            $table->foreign('document_type_id')->references('id')->on('dte_document_types')->onDelete('restrict');

            // Agregar índice
            $table->index('document_type_id');

            // Recrear la restricción unique con la nueva columna
            $table->unique(['warehouse_id', 'document_type_id', 'is_active'], 'unique_active_correlative_per_doc_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correlatives', function (Blueprint $table) {
            // Revertir cambios
            $table->dropUnique('unique_active_correlative_per_doc_type');
            $table->dropForeign(['document_type_id']);
            $table->dropIndex(['document_type_id']);
            $table->dropColumn('document_type_id');

            // Restaurar columna original
            $table->string('document_type', 50)->after('warehouse_id')->comment('Tipo de documento (factura, ticket, cotizacion, etc)');
            $table->index('document_type');
            $table->unique(['warehouse_id', 'document_type', 'is_active'], 'unique_active_correlative');
        });
    }
};
