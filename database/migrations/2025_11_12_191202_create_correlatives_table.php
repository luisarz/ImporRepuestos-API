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
        Schema::create('correlatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->comment('Sucursal');
            $table->string('document_type', 50)->comment('Tipo de documento (factura, ticket, cotizacion, etc)');
            $table->string('prefix', 20)->comment('Prefijo del correlativo (ej: FAC, TKT, COT)');
            $table->integer('current_number')->default(0)->comment('Número actual');
            $table->integer('start_number')->default(1)->comment('Número inicial');
            $table->integer('padding_length')->default(6)->comment('Longitud de ceros a la izquierda');
            $table->boolean('is_active')->default(true)->comment('Estado activo/inactivo');
            $table->text('description')->nullable()->comment('Descripción del correlativo');
            $table->timestamps();

            // Foreign keys
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');

            // Unique constraint - un solo correlativo activo por sucursal y tipo de documento
            $table->unique(['warehouse_id', 'document_type', 'is_active'], 'unique_active_correlative');

            // Indexes
            $table->index('warehouse_id');
            $table->index('document_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correlatives');
    }
};
