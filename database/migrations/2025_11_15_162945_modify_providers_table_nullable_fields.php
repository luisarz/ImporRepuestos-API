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
        Schema::table('providers', function (Blueprint $table) {
            // Hacer last_purchase nullable
            $table->date('last_purchase')->nullable()->change();

            // Cambiar decimal_purchase de integer a decimal
            $table->decimal('decimal_purchase', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            // Revertir los cambios
            $table->date('last_purchase')->nullable(false)->change();
            $table->integer('decimal_purchase')->change();
        });
    }
};
