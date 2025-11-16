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
        Schema::table('sale_items', function (Blueprint $table) {
            // Eliminar la foreign key incorrecta
            $table->dropForeign(['batch_id']);

            // Crear la foreign key correcta que apunte a batches.id
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Revertir: eliminar la foreign key correcta
            $table->dropForeign(['batch_id']);

            // Restaurar la foreign key incorrecta original
            $table->foreign('batch_id')->references('id')->on('inventories_batches');
        });
    }
};
