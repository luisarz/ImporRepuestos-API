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
        Schema::table('sale_payment_details', function (Blueprint $table) {
            $table->unsignedBigInteger('cash_opening_id')->nullable()->after('sale_id');
            $table->foreign('cash_opening_id')->references('id')->on('cash_openings')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_payment_details', function (Blueprint $table) {
            $table->dropForeign(['cash_opening_id']);
            $table->dropColumn('cash_opening_id');
        });
    }
};
