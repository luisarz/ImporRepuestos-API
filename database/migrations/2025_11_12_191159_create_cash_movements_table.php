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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_opening_id')->comment('Apertura de caja');
            $table->unsignedBigInteger('user_id')->comment('Usuario que registra el movimiento');
            $table->enum('type', ['income', 'expense'])->comment('Tipo: ingreso o egreso');
            $table->decimal('amount', 10, 2)->comment('Monto del movimiento');
            $table->string('concept', 200)->comment('Concepto del movimiento');
            $table->text('description')->nullable()->comment('Descripción detallada');
            $table->string('reference', 100)->nullable()->comment('Número de referencia/documento');
            $table->unsignedBigInteger('sale_id')->nullable()->comment('Venta relacionada (opcional)');
            $table->dateTime('movement_date')->comment('Fecha y hora del movimiento');
            $table->timestamps();

            // Foreign keys
            $table->foreign('cash_opening_id')->references('id')->on('cash_openings')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('sale_id')->references('id')->on('sales_headers')->onDelete('set null');

            // Indexes
            $table->index('cash_opening_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('movement_date');
            $table->index('sale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
