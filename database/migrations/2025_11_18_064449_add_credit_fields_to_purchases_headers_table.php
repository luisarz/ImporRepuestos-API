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
            $table->date('due_date')->nullable()->after('purchase_date');
            $table->integer('days_credit')->default(0)->after('due_date');
            $table->foreignId('operation_condition_id')->nullable()->constrained('operation_conditions')->after('days_credit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases_headers', function (Blueprint $table) {
            $table->dropForeign(['operation_condition_id']);
            $table->dropColumn(['due_date', 'days_credit', 'operation_condition_id']);
        });
    }
};
