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

        Schema::create('quote_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_method');
            $table->unsignedBigInteger('provider')->index();
            $table->date('date');
            $table->decimal('amount_purchase');
            $table->boolean('is_active');
            $table->boolean('is_purchaded');
            $table->boolean('is_compared');
            $table->unsignedBigInteger('buyer_id');
            $table->foreign('buyer_id')->references('id')->on('employees');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_purchases');
    }
};
