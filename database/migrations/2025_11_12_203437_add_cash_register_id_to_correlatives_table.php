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
            // Agregar la columna cash_register_id después de warehouse_id
            $table->unsignedBigInteger('cash_register_id')->nullable()->after('warehouse_id')->comment('Caja Registradora');

            // Agregar foreign key
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->onDelete('cascade');

            // Agregar índice
            $table->index('cash_register_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correlatives', function (Blueprint $table) {
            // Eliminar foreign key e índice
            $table->dropForeign(['cash_register_id']);
            $table->dropIndex(['cash_register_id']);

            // Eliminar columna
            $table->dropColumn('cash_register_id');
        });
    }
};
