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
            // Eliminar la foreign key incorrecta que apunta a 'quote_purchase' (singular)
            $table->dropForeign(['quote_purchase_id']);

            // Crear la foreign key correcta que apunta a 'quote_purchases' (plural)
            $table->foreign('quote_purchase_id')
                ->references('id')
                ->on('quote_purchases')
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
            $table->dropForeign(['quote_purchase_id']);

            // Restaurar la FK incorrecta (por si necesitamos rollback)
            $table->foreign('quote_purchase_id')
                ->references('id')
                ->on('quote_purchase');
        });
    }
};
