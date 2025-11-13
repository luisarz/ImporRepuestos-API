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
        Schema::table('history_dtes', function (Blueprint $table) {
            $table->index('codigoGeneracion', 'idx_history_dtes_codigo_generacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_dtes', function (Blueprint $table) {
            $table->dropIndex('idx_history_dtes_codigo_generacion');
        });
    }
};
