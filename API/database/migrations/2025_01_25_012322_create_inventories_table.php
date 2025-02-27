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

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->decimal('last_cost_without_tax');
            $table->decimal('last_cost_with_tax');
            $table->decimal('stock_actual_quantity')->comment('Sum quantity');
            $table->decimal('stock_min');
            $table->boolean('alert_stock_min');
            $table->decimal('stock_max');
            $table->boolean('alert_stock_max');
            $table->dateTime('last_purchase');
            $table->boolean('is_service');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['warehouse_id', 'product_id'], 'unique_warehouse_product');

        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique('unique_warehouse_product');
        });
        Schema::dropIfExists('inventories');
    }
};
