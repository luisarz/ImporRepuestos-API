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

        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('comercial_name');
            $table->unsignedBigInteger('document_type_id');
            $table->foreign('document_type_id')->references('id')->on('documents_types_providers');
            $table->string('document_number');
            $table->unsignedBigInteger('economic_activity_id');
            $table->foreign('economic_activity_id')->references('id')->on('economic_activities');
            $table->unsignedBigInteger('provider_type_id');
            $table->foreign('provider_type_id')->references('id')->on('providers_types');
            $table->unsignedBigInteger('payment_type_id');
            $table->integer('credit_days');
            $table->decimal('credit_limit')->index();
            $table->decimal('debit_balance');
            $table->date('last_purchase');
            $table->integer('decimal_purchase');
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
        Schema::dropIfExists('providers');
    }
};
