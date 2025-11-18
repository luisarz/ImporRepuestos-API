<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\CashOpening;
use App\Models\CashMovement;
use App\Models\CashDenominationCount;
use App\Models\SalesHeader;
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
     * Obtener reporte detallado de caja para cierre diario
     * Incluye ventas por método de pago, movimientos y conteo de denominaciones
     */
    public function getDetailedCashReport(int $openingId)
    {
        $opening = CashOpening::with([
            'cashRegister.warehouse',
            'user',
            'closingUser',
            'authorizedByUser',
            'cashMovements.user',
            'cashMovements.sale',
            'denominationCounts'
        ])->findOrFail($openingId);

        // Obtener ventas durante esta apertura
        $sales = SalesHeader::with(['paymentDetails.paymentMethod', 'paymentMethod'])
            ->whereBetween('created_at', [
                $opening->opened_at,
                $opening->closed_at ?? Carbon::now()
            ])
            ->where('warehouse_id', $opening->cashRegister->warehouse_id)
            ->get();

        // Desglose por método de pago
        $salesByPaymentMethod = [];
        $totalCashSales = 0;

        foreach ($sales as $sale) {
            // Verificar si la venta tiene paymentDetails (sistema nuevo: pagos divididos)
            if ($sale->paymentDetails && $sale->paymentDetails->count() > 0) {
                // Sistema nuevo: ventas con pagos divididos
                foreach ($sale->paymentDetails as $payment) {
                    $methodName = $payment->paymentMethod->name ?? 'Sin método';
                    $methodCode = $payment->paymentMethod->code ?? null;

                    if (!isset($salesByPaymentMethod[$methodName])) {
                        $salesByPaymentMethod[$methodName] = [
                            'count' => 0,
                            'total' => 0,
                            'method_id' => $payment->payment_method_id
                        ];
                    }

                    $salesByPaymentMethod[$methodName]['count']++;
                    $salesByPaymentMethod[$methodName]['total'] += $payment->payment_amount;

                    // Sumar ventas en efectivo (código 01 = Billetes y monedas según catálogo MH)
                    if ($methodCode === '01') {
                        $totalCashSales += $payment->payment_amount;
                    }
                }
            } else {
                // Sistema antiguo: payment_method_id directo en sales_headers
                $methodName = $sale->paymentMethod->name ?? 'Sin método';
                $methodCode = $sale->paymentMethod->code ?? null;

                if (!isset($salesByPaymentMethod[$methodName])) {
                    $salesByPaymentMethod[$methodName] = [
                        'count' => 0,
                        'total' => 0,
                        'method_id' => $sale->payment_method_id
                    ];
                }

                $salesByPaymentMethod[$methodName]['count']++;
                $salesByPaymentMethod[$methodName]['total'] += $sale->sale_total;

                // Sumar ventas en efectivo (código 01 = Billetes y monedas según catálogo MH)
                if ($methodCode === '01') {
                    $totalCashSales += $sale->sale_total;
                }
            }
        }

        // Calcular efectivo esperado
        $expectedCash = $this->calculateExpectedCash($opening, $totalCashSales);

        return [
            'opening' => $opening,
            'summary' => $opening->getMovementsSummary(),
            'sales' => [
                'total' => $sales->sum('sale_total'),
                'count' => $sales->count(),
                'by_payment_method' => $salesByPaymentMethod,
                'cash_sales' => $totalCashSales
            ],
            'cash_details' => [
                'expected_cash' => $expectedCash,
                'counted_cash' => $opening->closing_amount ?? 0,
                'denominations' => $opening->denominationCounts->groupBy('type')
            ],
            'expected_amount' => $opening->calculateExpectedAmount(),
        ];
    }

    /**
     * Calcular el efectivo esperado en caja
     */
    private function calculateExpectedCash($opening, $cashSales)
    {
        $cashIncome = $opening->cashMovements()
            ->where('type', 'income')
            ->sum('amount');

        $cashExpense = $opening->cashMovements()
            ->where('type', 'expense')
            ->sum('amount');

        return $opening->opening_amount + $cashSales + $cashIncome - $cashExpense;
    }

    /**
     * Cerrar caja con conteo de denominaciones
     */
    public function closeWithDenominations(int $openingId, array $data)
    {
        DB::beginTransaction();
        try {
            $opening = CashOpening::findOrFail($openingId);

            if ($opening->isClosed()) {
                throw new Exception('Esta caja ya está cerrada');
            }

            // Guardar conteo de denominaciones
            if (isset($data['denominations']) && is_array($data['denominations'])) {
                foreach ($data['denominations'] as $denom) {
                    if (isset($denom['value']) && isset($denom['quantity']) && $denom['quantity'] > 0) {
                        CashDenominationCount::create([
                            'cash_opening_id' => $openingId,
                            'denomination' => $denom['value'],
                            'quantity' => $denom['quantity'],
                            'total' => $denom['value'] * $denom['quantity'],
                            'type' => $denom['type'] ?? 'bill'
                        ]);
                    }
                }
            }

            // Calcular montos
            $expectedAmount = $opening->calculateExpectedAmount();
            $closingAmount = $data['closing_amount'];
            $difference = $closingAmount - $expectedAmount;

            // Validar si necesita autorización
            $tolerance = $data['tolerance'] ?? 5;
            $requiresAuthorization = abs($difference) > $tolerance;

            if ($requiresAuthorization && !isset($data['authorized_by'])) {
                throw new Exception('Se requiere autorización de supervisor para esta diferencia');
            }

            // Actualizar apertura con cierre
            $opening->update([
                'closing_amount' => $closingAmount,
                'expected_amount' => $expectedAmount,
                'difference_amount' => $difference,
                'closing_notes' => $data['closing_notes'] ?? null,
                'closed_at' => Carbon::now(),
                'status' => 'closed',
                'closing_user_id' => $data['user_id'],
                'authorized_by' => $data['authorized_by'] ?? null,
                'authorization_notes' => $data['authorization_notes'] ?? null
            ]);

            DB::commit();
            return $opening->fresh([
                'denominationCounts',
                'closingUser',
                'authorizedByUser',
                'cashRegister',
                'user'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
