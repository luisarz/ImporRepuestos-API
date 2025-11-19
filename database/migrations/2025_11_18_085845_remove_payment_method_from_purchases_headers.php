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
        Schema::table('purchases_headers', function (Blueprint $table) {
            // Eliminar la columna payment_method
            $table->dropColumn('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases_headers', function (Blueprint $table) {
            // Restaurar la columna payment_method en caso de rollback
            $table->enum('payment_method', ["1","2"])->default('1')->comment('1=Contado, 2=Credito')->after('purchase_type');
        });
    }
};
