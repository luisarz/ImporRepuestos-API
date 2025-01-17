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

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedBigInteger('inventory_id')->index();
            $table->date('incoming_date');
            $table->date('expiration_date');
            $table->decimal('initial_quantity');
            $table->decimal('available_quantity');
            $table->string('observations');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
