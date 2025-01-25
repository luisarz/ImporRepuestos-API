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

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->unsignedBigInteger('model_id')->index();
            $table->foreign('model_id')->references('id')->on('vehicle_models');
            $table->string('model_two');
            $table->string('year');
            $table->string('chassis');
            $table->string('vin');
            $table->string('motor');
            $table->string('displacement');
            $table->string('motor_type');
            $table->unsignedBigInteger('fuel_type');
            $table->foreign('fuel_type')->references('id')->on('fuel_types');
            $table->string('vehicle_class');
            $table->date('income_date');
            $table->bigInteger('municipality_id');
            $table->string('antique');
            $table->unsignedBigInteger('plate_type');
            $table->foreign('plate_type')->references('id')->on('plate_types');
            $table->decimal('capacity');
            $table->decimal('tonnage');
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
        Schema::dropIfExists('vehicles');
    }
};
