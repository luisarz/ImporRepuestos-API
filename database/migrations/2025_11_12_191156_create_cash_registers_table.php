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
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Código único de la caja');
            $table->string('name', 100)->comment('Nombre de la caja');
            $table->unsignedBigInteger('warehouse_id')->comment('Sucursal a la que pertenece');
            $table->text('description')->nullable()->comment('Descripción de la caja');
            $table->boolean('is_active')->default(true)->comment('Estado activo/inactivo');
            $table->timestamps();

            // Foreign keys
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');

            // Indexes
            $table->index('warehouse_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
