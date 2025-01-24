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

        Schema::create('quote_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qoute_purchase_id')->index();
            $table->foreign('qoute_purchase_id')->references('id')->on('quote_purchase');
            $table->unsignedBigInteger('inventory_id')->index();
            $table->foreign('inventory_id')->references('id')->on('warehouses');
            $table->decimal('quantity');
            $table->decimal('price');
            $table->decimal('discount');
            $table->decimal('total');
            $table->bigInteger('is_compared');
            $table->boolean('is_purchaseded');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_purchase_items');
    }
};
