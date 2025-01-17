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

        Schema::create('sales_headers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cashbox_open_id');
            $table->dateTime('sale_date');
            $table->bigInteger('warehouse_id');
            $table->unsignedBigInteger('document_type_id');
            $table->bigInteger('document_internal_number');
            $table->unsignedBigInteger('seller_id');
            $table->foreign('seller_id')->references('id')->on('employees');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedBigInteger('operation_condition_id');
            $table->enum('sale_status', ["1","2","3"])->default('1')->comment('1=Procesando,2=Finalizada,3=Anulada');
            $table->boolean('have_retention');
            $table->decimal('net_amount');
            $table->decimal('taxe');
            $table->decimal('discount');
            $table->decimal('retention');
            $table->decimal('sale_total');
            $table->bigInteger('payment_status')->default(1)->comment('1=Pagada,2=Parcial,3=Pendiente');
            $table->boolean('is_order');
            $table->boolean('is_order_closed_without_invoiced');
            $table->boolean('is_invoiced_order');
            $table->decimal('discount_percentage');
            $table->decimal('discount_money');
            $table->decimal('total_order_after_discount');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_headers');
    }
};
