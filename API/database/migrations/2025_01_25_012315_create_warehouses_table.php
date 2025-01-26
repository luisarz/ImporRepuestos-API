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

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id()->foreign('company.id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('stablishment_type');
            $table->foreign('stablishment_type')->references('id')->on('stablishment_types');
            $table->string('name');
            $table->string('nrc');
            $table->string('nit');
            $table->unsignedBigInteger('district_id');
            $table->foreign('district_id')->references('id')->on('districts');
            $table->unsignedBigInteger('economic_activity_id');
            $table->foreign('economic_activity_id')->references('id')->on('economic_activities');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->integer('product_prices')->default(2);
            $table->json('logo')->nullable();
            $table->boolean('is_active');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
