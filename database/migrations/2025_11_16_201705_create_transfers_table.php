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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->date('transfer_date');
            $table->unsignedBigInteger('warehouse_origin_id');
            $table->unsignedBigInteger('warehouse_destination_id');
            $table->enum('status', ['PENDING', 'IN_TRANSIT', 'RECEIVED', 'CANCELLED'])->default('PENDING');
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('warehouse_origin_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('warehouse_destination_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('transfer_number');
            $table->index('status');
            $table->index('transfer_date');
            $table->index('warehouse_origin_id');
            $table->index('warehouse_destination_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
