<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases_headers', function (Blueprint $table) {
            // Agregar campo pending_balance después de total_purchase
            $table->decimal('pending_balance', 10, 2)->default(0)->after('total_purchase');

            // Modificar ENUM de payment_status para incluir '0'
            // Nota: MySQL requiere redefinir todo el ENUM
            DB::statement("ALTER TABLE purchases_headers MODIFY COLUMN payment_status ENUM('0','1','2') DEFAULT '1' COMMENT '0=Pendiente, 1=Parcial, 2=Pagada'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases_headers', function (Blueprint $table) {
            // Eliminar campo pending_balance
            $table->dropColumn('pending_balance');

            // Restaurar ENUM original
            DB::statement("ALTER TABLE purchases_headers MODIFY COLUMN payment_status ENUM('1','2','3') DEFAULT '1' COMMENT '1=Pagada, 2=Parcial, 3=Pendiente'");
        });
    }
};
