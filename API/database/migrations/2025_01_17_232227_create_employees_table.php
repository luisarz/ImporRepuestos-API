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

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unsignedBigInteger('job_title_id');
            $table->foreign('job_title_id')->references('id')->on('jobs_titles');
            $table->string('name');
            $table->string('last_name');
            $table->enum('gender', ["M","F"])->default('M');
            $table->string('dui');
            $table->string('nit');
            $table->string('phone');
            $table->string('email');
            $table->json('photo')->nullable();
            $table->unsignedBigInteger('district_id');
            $table->foreign('district_id')->references('id')->on('districts');
            $table->string('address');
            $table->string('comision_porcentage');
            $table->boolean('is_active');
            $table->enum('marital_status', ["Soltero\/a","Casado\/a","Divorciado\/a","Viudo"])->default('Soltero/a');
            $table->string('marital_name');
            $table->string('marital_phone');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
