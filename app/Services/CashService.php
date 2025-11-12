<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\CashOpening;
use App\Models\CashMovement;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CashService
{
    /**
     * Abrir una caja registradora
     */
    public function openCash(array $data)
    {
        DB::beginTransaction();
        try {
            // Verificar que la caja existe y está activa
            $cashRegister = CashRegister::findOrFail($data['cash_register_id']);

            if (!$cashRegister->is_active) {
                throw new Exception('La caja registradora no está activa');
            }

            // Verificar que no haya una apertura activa
            if ($cashRegister->hasOpenCash()) {
                throw new Exception('La caja ya tiene una apertura activa');
            }

            // Crear la apertura
            $opening = CashOpening::create([
                'cash_register_id' => $data['cash_register_id'],
                'user_id' => $data['user_id'],
                'opened_at' => Carbon::now(),
                'opening_amount' => $data['opening_amount'] ?? 0,
                'opening_notes' => $data['opening_notes'] ?? null,
                'status' => 'open',
            ]);

            DB::commit();
            return $opening->load(['cashRegister', 'user']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cerrar una apertura de caja
     */
    public function closeCash(int $openingId, array $data)
    {
        DB::beginTransaction();
        try {
            $opening = CashOpening::findOrFail($openingId);

            // Verificar que la apertura esté abierta
            if ($opening->isClosed()) {
                throw new Exception('Esta apertura ya está cerrada');
            }

            // Calcular el monto esperado
            $expectedAmount = $opening->calculateExpectedAmount();

            // Calcular la diferencia
            $closingAmount = $data['closing_amount'];
            $differenceAmount = $closingAmount - $expectedAmount;

            // Actualizar la apertura
            $opening->update([
                'closed_at' => Carbon::now(),
                'closing_amount' => $closingAmount,
                'expected_amount' => $expectedAmount,
                'difference_amount' => $differenceAmount,
                'closing_notes' => $data['closing_notes'] ?? null,
                'status' => 'closed',
            ]);

            DB::commit();
            return $opening->load(['cashRegister', 'user']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Registrar un movimiento de caja
     */
    public function registerMovement(array $data)
    {
        DB::beginTransaction();
        try {
            // Verificar que la apertura existe y está abierta
            $opening = CashOpening::findOrFail($data['cash_opening_id']);

            if ($opening->isClosed()) {
                throw new Exception('No se pueden registrar movimientos en una caja cerrada');
            }

            // Crear el movimiento
            $movement = CashMovement::create([
                'cash_opening_id' => $data['cash_opening_id'],
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'amount' => $data['amount'],
                'concept' => $data['concept'],
                'description' => $data['description'] ?? null,
                'reference' => $data['reference'] ?? null,
                'sale_id' => $data['sale_id'] ?? null,
                'movement_date' => $data['movement_date'] ?? Carbon::now(),
            ]);

            DB::commit();
            return $movement->load(['cashOpening', 'user', 'sale']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener el reporte de una apertura de caja
     */
    public function getCashReport(int $openingId)
    {
        $opening = CashOpening::with([
            'cashRegister',
            'user',
            'cashMovements.user',
            'cashMovements.sale'
        ])->findOrFail($openingId);

        $summary = $opening->getMovementsSummary();

        return [
            'opening' => $opening,
            'summary' => $summary,
            'expected_amount' => $opening->calculateExpectedAmount(),
        ];
    }

    /**
     * Obtener estadísticas de caja por rango de fechas
     */
    public function getCashStatsByDateRange($warehouseId, $startDate, $endDate)
    {
        $openings = CashOpening::whereHas('cashRegister', function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->whereBetween('opened_at', [$startDate, $endDate])
            ->with(['cashRegister', 'user', 'cashMovements'])
            ->get();

        $stats = [
            'total_openings' => $openings->count(),
            'total_closed' => $openings->where('status', 'closed')->count(),
            'total_open' => $openings->where('status', 'open')->count(),
            'total_opening_amount' => $openings->sum('opening_amount'),
            'total_closing_amount' => $openings->where('status', 'closed')->sum('closing_amount'),
            'total_difference' => $openings->where('status', 'closed')->sum('difference_amount'),
        ];

        return [
            'openings' => $openings,
            'stats' => $stats,
        ];
    }

    /**
     * Verificar si un usuario tiene una caja abierta
     */
    public function getUserOpenCash($userId)
    {
        return CashOpening::where('user_id', $userId)
            ->where('status', 'open')
            ->with(['cashRegister', 'cashMovements'])
            ->first();
    }

    /**
     * Obtener movimientos de una apertura con filtros
     */
    public function getMovementsWithFilters(int $openingId, array $filters = [])
    {
        $query = CashMovement::where('cash_opening_id', $openingId)
            ->with(['user', 'sale']);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('movement_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('movement_date', 'desc')->get();
    }
}
