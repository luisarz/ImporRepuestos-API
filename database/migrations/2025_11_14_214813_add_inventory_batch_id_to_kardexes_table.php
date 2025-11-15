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
        Schema::table('kardexes', function (Blueprint $table) {
            $table->foreignId('inventory_batch_id')
                ->nullable()
                ->after('inventory_id')
                ->constrained('inventories_batches')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kardexes', function (Blueprint $table) {
            $table->dropForeign(['inventory_batch_id']);
            $table->dropColumn('inventory_batch_id');
        });
    }
};
