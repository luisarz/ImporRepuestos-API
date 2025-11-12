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
        Schema::create('cash_openings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_register_id')->comment('Caja registradora');
            $table->unsignedBigInteger('user_id')->comment('Usuario que abre la caja');
            $table->dateTime('opened_at')->comment('Fecha y hora de apertura');
            $table->dateTime('closed_at')->nullable()->comment('Fecha y hora de cierre');
            $table->decimal('opening_amount', 10, 2)->default(0)->comment('Monto inicial');
            $table->decimal('closing_amount', 10, 2)->nullable()->comment('Monto final');
            $table->decimal('expected_amount', 10, 2)->nullable()->comment('Monto esperado al cierre');
            $table->decimal('difference_amount', 10, 2)->nullable()->comment('Diferencia (cierre - esperado)');
            $table->text('opening_notes')->nullable()->comment('Notas de apertura');
            $table->text('closing_notes')->nullable()->comment('Notas de cierre');
            $table->enum('status', ['open', 'closed'])->default('open')->comment('Estado de la apertura');
            $table->timestamps();

            // Foreign keys
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            // Indexes
            $table->index('cash_register_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_openings');
    }
};
