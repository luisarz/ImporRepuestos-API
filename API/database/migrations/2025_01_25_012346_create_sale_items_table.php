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

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales_header');
            $table->bigInteger('inventory_id');
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('id')->on('inventories_batches');
            $table->boolean('saled');
            $table->decimal('quantity');
            $table->decimal('price');
            $table->decimal('discount');
            $table->decimal('total');
            $table->boolean('is_saled');
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
        Schema::dropIfExists('sale_items');
    }
};
