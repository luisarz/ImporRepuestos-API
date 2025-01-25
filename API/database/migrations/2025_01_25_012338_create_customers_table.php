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

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_type');
            $table->string('internal_code');
            $table->foreign('internal_code')->references('id')->on('customer_types');
            $table->unsignedBigInteger('document_type_id');
            $table->foreign('document_type_id')->references('id')->on('customer_documents_types');
            $table->string('document_number');
            $table->string('name');
            $table->string('last_name');
            $table->unsignedBigInteger('warehouse');
            $table->foreign('warehouse')->references('id')->on('warehouses');
            $table->string('nrc');
            $table->string('nit');
            $table->boolean('is_exempt');
            $table->enum('sales_type', ["1","2","3","4"])->default('1')->comment('1=Mayoreo; 2=Detalle; 3=Taller;');
            $table->boolean('is_creditable');
            $table->string('address');
            $table->decimal('credit_limit');
            $table->decimal('credit_amount');
            $table->boolean('is_delivery');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
