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
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('inventory_origin_id');
            $table->unsignedBigInteger('inventory_destination_id')->nullable();
            $table->unsignedBigInteger('batch_id');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2);
            $table->enum('status', ['PENDING', 'SENT', 'RECEIVED'])->default('PENDING');
            $table->timestamps();

            // Foreign keys
            $table->foreign('transfer_id')->references('id')->on('transfers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('inventory_origin_id')->references('id')->on('inventories')->onDelete('restrict');
            $table->foreign('inventory_destination_id')->references('id')->on('inventories')->onDelete('restrict');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('restrict');

            // Indexes
            $table->index('transfer_id');
            $table->index('product_id');
            $table->index('batch_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_items');
    }
};
