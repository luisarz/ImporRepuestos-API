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
            $table->bigInteger('billing_model');
            $table->bigInteger('transmision_type');
            $table->bigInteger('receipt_stamp');
            $table->bigInteger('json_url')->nullable();
            $table->bigInteger('pdf_url');
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
