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
        Schema::create('purchase_payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases_headers')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->foreignId('casher_id')->constrained('employees');
            $table->decimal('payment_amount', 10, 2);
            $table->decimal('actual_balance', 10, 2)->default(0);
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->string('reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Índices para mejorar consultas
            $table->index('purchase_id');
            $table->index('payment_method_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payment_details');
    }
};
