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
            // Eliminar la foreign key existente
            $table->dropForeign(['batch_id']);

            // Modificar la columna para que sea nullable
            $table->unsignedBigInteger('batch_id')->nullable()->change();

            // Recrear la foreign key
            $table->foreign('batch_id')->references('id')->on('inventories_batches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Eliminar la foreign key
            $table->dropForeign(['batch_id']);

            // Revertir la columna a NOT NULL
            $table->unsignedBigInteger('batch_id')->nullable(false)->change();

            // Recrear la foreign key
            $table->foreign('batch_id')->references('id')->on('inventories_batches');
        });
    }
};
