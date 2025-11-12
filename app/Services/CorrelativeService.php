<?php

namespace App\Services;

use App\Models\Correlative;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class CorrelativeService
{
    /**
     * Obtener el siguiente número correlativo
     */
    public function getNextNumber($warehouseId, $documentType): string
    {
        DB::beginTransaction();
        try {
            $correlative = Correlative::getActiveCorrelative($warehouseId, $documentType);

            if (!$correlative) {
                throw new Exception("No hay un correlativo activo configurado para {$documentType} en esta sucursal");
            }

            // Generar el siguiente número
            $nextNumber = $correlative->generateNext();

            // Incrementar el contador
            $correlative->increment();

            DB::commit();
            return $nextNumber;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener el correlativo actual sin incrementar
     */
    public function getCurrentNumber($warehouseId, $documentType): ?string
    {
        $correlative = Correlative::getActiveCorrelative($warehouseId, $documentType);

        if (!$correlative) {
            return null;
        }

        return $correlative->getCurrentFormatted();
    }

    /**
     * Crear un nuevo correlativo
     */
    public function createCorrelative(array $data)
    {
        DB::beginTransaction();
        try {
            // Verificar que la sucursal existe
            $warehouse = Warehouse::findOrFail($data['warehouse_id']);

            // Si se marca como activo, desactivar cualquier otro correlativo activo para el mismo tipo de documento
            if ($data['is_active'] ?? true) {
                Correlative::where('warehouse_id', $data['warehouse_id'])
                    ->where('document_type', $data['document_type'])
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            // Crear el correlativo
            $correlative = Correlative::create([
                'warehouse_id' => $data['warehouse_id'],
                'document_type' => $data['document_type'],
                'prefix' => $data['prefix'],
                'current_number' => $data['start_number'] ?? 1,
                'start_number' => $data['start_number'] ?? 1,
                'padding_length' => $data['padding_length'] ?? 6,
                'is_active' => $data['is_active'] ?? true,
                'description' => $data['description'] ?? null,
            ]);

            DB::commit();
            return $correlative->load('warehouse');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar un correlativo
     */
    public function updateCorrelative(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $correlative = Correlative::findOrFail($id);

            // Si se está activando este correlativo, desactivar otros del mismo tipo
            if (isset($data['is_active']) && $data['is_active']) {
                Correlative::where('warehouse_id', $correlative->warehouse_id)
                    ->where('document_type', $correlative->document_type)
                    ->where('id', '!=', $id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $correlative->update($data);

            DB::commit();
            return $correlative->load('warehouse');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Resetear un correlativo al número inicial
     */
    public function resetCorrelative(int $id)
    {
        DB::beginTransaction();
        try {
            $correlative = Correlative::findOrFail($id);
            $correlative->reset();

            DB::commit();
            return $correlative;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Activar/Desactivar un correlativo
     */
    public function toggleCorrelative(int $id, bool $isActive)
    {
        DB::beginTransaction();
        try {
            $correlative = Correlative::findOrFail($id);

            // Si se está activando, desactivar otros del mismo tipo
            if ($isActive) {
                Correlative::where('warehouse_id', $correlative->warehouse_id)
                    ->where('document_type', $correlative->document_type)
                    ->where('id', '!=', $id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $correlative->update(['is_active' => $isActive]);

            DB::commit();
            return $correlative;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener correlativos por sucursal
     */
    public function getByWarehouse($warehouseId)
    {
        return Correlative::where('warehouse_id', $warehouseId)
            ->with('warehouse')
            ->orderBy('document_type')
            ->get();
    }

    /**
     * Validar que existe un correlativo activo
     */
    public function validateCorrelativeExists($warehouseId, $documentType): bool
    {
        return Correlative::where('warehouse_id', $warehouseId)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Obtener tipos de documentos disponibles
     */
    public function getDocumentTypes(): array
    {
        return [
            'factura' => 'Factura',
            'ticket' => 'Ticket',
            'cotizacion' => 'Cotización',
            'nota_credito' => 'Nota de Crédito',
            'nota_debito' => 'Nota de Débito',
            'proforma' => 'Proforma',
            'orden_compra' => 'Orden de Compra',
            'recibo' => 'Recibo',
        ];
    }
}
