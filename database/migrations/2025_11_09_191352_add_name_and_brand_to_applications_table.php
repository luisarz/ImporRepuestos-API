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
        Schema::table('applications', function (Blueprint $table) {
            $table->string('name')->nullable()->after('vehicle_id');
            $table->string('brand')->nullable()->after('name');
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['name', 'brand']);
            $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
        });
    }
};
