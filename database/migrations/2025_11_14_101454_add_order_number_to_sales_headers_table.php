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
        Schema::table('sales_headers', function (Blueprint $table) {
            $table->integer('order_number')->nullable()->after('document_internal_number')->comment('Número secuencial de orden por sucursal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_headers', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};
