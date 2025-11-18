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
        Schema::table('sale_payment_details', function (Blueprint $table) {
            // Eliminar foreign key incorrecta
            $table->dropForeign('sale_payment_details_sale_id_foreign');

            // Crear foreign key correcta apuntando a sales_headers
            $table->foreign('sale_id')->references('id')->on('sales_headers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_payment_details', function (Blueprint $table) {
            // Revertir: eliminar la foreign key correcta
            $table->dropForeign(['sale_id']);

            // Re-crear la foreign key incorrecta (para poder revertir)
            $table->foreign('sale_id')->references('id')->on('sales_header');
        });
    }
};
