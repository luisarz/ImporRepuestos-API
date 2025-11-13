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
        Schema::create('dte_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_header_id')->nullable()->constrained('sales_headers')->onDelete('set null')->comment('ID de la venta');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Usuario que intentó generar el DTE');
            $table->string('document_type', 50)->nullable()->comment('Tipo de documento (CCF, Factura, etc.)');
            $table->string('action', 100)->comment('Acción realizada (generarDTE, anularDTE, etc.)');
            $table->enum('status', ['EXITO', 'FALLO', 'RECHAZADO', 'PENDIENTE'])->default('FALLO')->comment('Estado del proceso');
            $table->string('error_code', 50)->nullable()->comment('Código de error');
            $table->text('error_message')->nullable()->comment('Mensaje de error detallado');
            $table->json('request_data')->nullable()->comment('Datos del request enviado');
            $table->json('response_data')->nullable()->comment('Respuesta recibida del servicio de DTE');
            $table->text('stack_trace')->nullable()->comment('Stack trace del error (solo para debugging)');
            $table->string('generation_code', 100)->nullable()->comment('Código de generación del DTE si fue creado');
            $table->string('ip_address', 45)->nullable()->comment('Dirección IP del usuario');
            $table->text('user_agent')->nullable()->comment('User agent del navegador');
            $table->integer('retry_count')->default(0)->comment('Número de reintentos realizados');
            $table->timestamp('resolved_at')->nullable()->comment('Fecha en que se resolvió el problema');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null')->comment('Usuario que resolvió el problema');
            $table->text('resolution_notes')->nullable()->comment('Notas sobre cómo se resolvió');
            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar búsquedas
            $table->index(['sales_header_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('generation_code');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dte_audit_logs');
    }
};
