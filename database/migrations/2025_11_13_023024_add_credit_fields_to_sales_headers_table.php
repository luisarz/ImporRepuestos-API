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
            $table->integer('credit_days')->nullable()->after('payment_status')->comment('Días de crédito otorgados');
            $table->date('due_date')->nullable()->after('credit_days')->comment('Fecha de vencimiento del crédito');
            $table->decimal('pending_balance', 10, 2)->default(0)->after('due_date')->comment('Saldo pendiente de pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_headers', function (Blueprint $table) {
            $table->dropColumn(['credit_days', 'due_date', 'pending_balance']);
        });
    }
};
