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
        Schema::table('cash_openings', function (Blueprint $table) {
            // Usuario que realizó el cierre (puede ser diferente del que abrió)
            $table->foreignId('closing_user_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');

            // Usuario que autorizó el cierre (en caso de diferencias)
            $table->foreignId('authorized_by')->nullable()->after('closing_user_id')->constrained('users')->onDelete('set null');

            // Notas de autorización
            $table->text('authorization_notes')->nullable()->after('closing_notes');

            // Ruta del PDF generado
            $table->string('closure_pdf_path')->nullable()->after('authorization_notes');

            // Índices
            $table->index('closing_user_id');
            $table->index('authorized_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_openings', function (Blueprint $table) {
            $table->dropForeign(['closing_user_id']);
            $table->dropForeign(['authorized_by']);
            $table->dropIndex(['closing_user_id']);
            $table->dropIndex(['authorized_by']);
            $table->dropColumn(['closing_user_id', 'authorized_by', 'authorization_notes', 'closure_pdf_path']);
        });
    }
};
