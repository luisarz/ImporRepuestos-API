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

        Schema::create('purchases_headers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse');
            $table->foreign('warehouse')->references('id')->on('warehouses');
            $table->unsignedBigInteger('provider_id');
            $table->foreign('provider_id')->references('id')->on('providers');
            $table->date('purchcase_date');
            $table->string('serie');
            $table->string('purchase_number');
            $table->string('resolution');
            $table->bigInteger('purchase_type');
            $table->enum('payment_method', ["1","2"])->default('1')->comment('1=Contado, 2=Credito');
            $table->enum('payment_status', ["1","2","3"])->default('1')->comment('1=Pagada, 2=Parcial, 3=Pendiente');
            $table->decimal('net_amount');
            $table->decimal('tax_amount');
            $table->decimal('retention_amount');
            $table->decimal('total_purchase');
            $table->unsignedBigInteger('employee_id');
            $table->enum('status_purchase', ["1","2","3"])->default('1')->comment('1 =Procesando, 2=Finzalizada,3=Anulada');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases_headers');
    }
};
