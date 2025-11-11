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
        Schema::table('customer_address_catalogs', function (Blueprint $table) {
            // Eliminar columnas antiguas
            $table->dropColumn([
                'address_reference',
                'is_active',
                'email',
                'phone',
                'contact',
                'contact_phone',
                'contact_email'
            ]);

            // Hacer district_id nullable
            $table->unsignedBigInteger('district_id')->nullable()->change();

            // Agregar nuevas columnas
            $table->text('address')->after('customer_id');
            $table->string('city', 100)->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('state');
            $table->string('country', 100)->nullable()->after('postal_code');
            $table->boolean('is_default')->default(0)->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_address_catalogs', function (Blueprint $table) {
            // Revertir: eliminar nuevas columnas
            $table->dropColumn([
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'is_default'
            ]);

            // Revertir: restaurar columnas antiguas
            $table->string('address_reference')->after('customer_id');
            $table->boolean('is_active')->after('address_reference');
            $table->string('email')->nullable()->after('is_active');
            $table->string('phone')->nullable()->after('email');
            $table->string('contact')->nullable()->after('phone');
            $table->string('contact_phone')->nullable()->after('contact');
            $table->string('contact_email')->nullable()->after('contact_phone');

            // Revertir: hacer district_id no nullable
            $table->unsignedBigInteger('district_id')->nullable(false)->change();
        });
    }
};
