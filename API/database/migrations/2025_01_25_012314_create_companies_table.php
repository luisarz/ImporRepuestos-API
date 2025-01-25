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

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('district_id');
            $table->foreign('district_id')->references('id')->on('districts');
            $table->unsignedBigInteger('economic_activity_id');
            $table->foreign('economic_activity_id')->references('id')->on('economic_activities');
            $table->string('company_name');
            $table->string('nrc');
            $table->string('nit');
            $table->string('phone');
            $table->string('whatsapp');
            $table->string('email');
            $table->string('address');
            $table->bigInteger('web');
            $table->string('api_key_mh');
            $table->json('logo')->nullable();
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
        Schema::dropIfExists('companies');
    }
};
