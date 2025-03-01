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

        Schema::create('sales_dtes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales_header');
            $table->boolean('is_dte');
            $table->bigInteger('generation_code');
            $table->unsignedBigInteger('billing_model');
            $table->bigInteger('transmition_type');
            $table->string('receipt_stamp');
            $table->string('json_url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_dtes');
    }
};
