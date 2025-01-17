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

        Schema::create('history_dtes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_dte_id');
            $table->foreign('sale_dte_id')->references('id')->on('sales_dte');
            $table->string('version');
            $table->string('ambiente');
            $table->enum('status', ["1","2"])->default('1')->comment('1=RECHAZADO,2=PROCESADO');
            $table->string('cod_geneneration');
            $table->string('receipt_stamp');
            $table->string('fhProcesamiento');
            $table->string('clasificaMsg');
            $table->string('codigoMsg');
            $table->string('descripcionMsg');
            $table->string('observaciones');
            $table->string('dte');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_dtes');
    }
};
