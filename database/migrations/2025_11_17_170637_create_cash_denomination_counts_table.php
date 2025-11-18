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
        Schema::create('cash_denomination_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_opening_id')->constrained('cash_openings')->onDelete('cascade');
            $table->decimal('denomination', 10, 2)->comment('Valor de la denominación (100, 50, 20, 10, 5, 1, 0.50, etc.)');
            $table->integer('quantity')->default(0)->comment('Cantidad de billetes o monedas');
            $table->decimal('total', 10, 2)->comment('denomination * quantity');
            $table->enum('type', ['bill', 'coin'])->comment('Tipo: billete o moneda');
            $table->timestamps();

            // Índices
            $table->index('cash_opening_id');
            $table->index(['cash_opening_id', 'denomination']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_denomination_counts');
    }
};
