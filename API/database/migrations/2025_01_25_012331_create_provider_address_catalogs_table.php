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

        Schema::create('provider_address_catalogs', function (Blueprint $table) {
            $table->id();
           $table->unsignedBigInteger('provider_id');
            $table->foreign('provider_id')->references('id')->on('providers');
           $table->unsignedBigInteger('district_id');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->string('address_reference');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('seller')->nullable();
            $table->string('seller_phone')->nullable();
            $table->string('seller_email')->nullable();
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
        Schema::dropIfExists('provider_address_catalogs');
    }
};