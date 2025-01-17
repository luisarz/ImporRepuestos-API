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
            $table->bigInteger('saled');
            $table->decimal('quantity');
            $table->bigInteger('price');
            $table->bigInteger('discount');
            $table->decimal('total');
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
