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
        Schema::create('destination_environments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique()->comment('Código del ambiente de destino (2 caracteres)');
            $table->string('description', 100)->comment('Descripción del ambiente de destino');
            $table->boolean('is_active')->default(true)->comment('Indica si el ambiente está activo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_environments');
    }
};
