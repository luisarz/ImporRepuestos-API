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
        Schema::table('quote_purchase_items', function (Blueprint $table) {
            // Eliminar la foreign key incorrecta que apunta a warehouses
            $table->dropForeign(['inventory_id']);

            // Crear la foreign key correcta que apunta a inventories
            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_purchase_items', function (Blueprint $table) {
            // Revertir: eliminar la FK correcta
            $table->dropForeign(['inventory_id']);

            // Restaurar la FK incorrecta (por si necesitamos rollback)
            $table->foreign('inventory_id')
                ->references('id')
                ->on('warehouses');
        });
    }
};
