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
        Schema::disableForeignKeyConstraints();

        Schema::create('sale_payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales_header');
            $table->bigInteger('payment_method_id');
            $table->unsignedBigInteger('casher_id');
            $table->foreign('casher_id')->references('id')->on('employees');
            $table->decimal('payment_amount');
            $table->decimal('actual_balance');
            $table->unsignedBigInteger('bank_account_id');
            $table->string('reference');
            $table->boolean('is_active');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payment_details');
    }
};
