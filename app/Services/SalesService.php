<?php

namespace App\Services;

use App\Models\SalesHeader;
use App\Models\SalePaymentDetail;
use App\Models\CashMovement;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesService
{
    /**
     * Registrar un pago/abono para una venta
     */
    public function registerPayment(int $saleId, array $paymentData)
    {
        DB::beginTransaction();
        try {
            $sale = SalesHeader::with('paymentDetails')->findOrFail($saleId);

            // Validar que la venta permita pagos (no debe estar cancelada)
            if ($sale->sale_status == 3) { // 3 = Cancelada
                throw new Exception('No se pueden registrar pagos en una venta cancelada');
            }

            // Calcular el saldo actual
            $totalPaid = $sale->paymentDetails->sum('payment_amount');
            $actualBalance = $sale->sale_total - $totalPaid;

            // Validar que el pago no exceda el saldo
            if ($paymentData['payment_amount'] > $actualBalance) {
                throw new Exception("El monto del pago ($" . $paymentData['payment_amount'] . ") excede el saldo pendiente ($" . $actualBalance . ")");
            }

            // Calcular nuevo saldo
            $newBalance = $actualBalance - $paymentData['payment_amount'];

            // Crear el registro de pago
            $payment = SalePaymentDetail::create([
                'sale_id' => $saleId,
                'payment_method_id' => $paymentData['payment_method_id'],
                'casher_id' => $paymentData['casher_id'],
                'payment_amount' => $paymentData['payment_amount'],
                'actual_balance' => $newBalance,
                'bank_account_id' => $paymentData['bank_account_id'] ?? null,
                'reference' => $paymentData['reference'] ?? null,
                'is_active' => true,
            ]);

            // Actualizar el estado de pago de la venta
            $paymentStatus = $this->calculatePaymentStatus($sale->sale_total, $totalPaid + $paymentData['payment_amount']);

            $sale->update([
                'pending_balance' => $newBalance,
                'payment_status' => $paymentStatus
            ]);

            // Si el pago es en efectivo (código 01), registrar movimiento de caja
            $paymentMethod = \App\Models\PaymentMethod::find($paymentData['payment_method_id']);
            if ($paymentMethod && $paymentMethod->code === '01') {
                $this->registerCashMovement($sale, $paymentData['payment_amount'], $paymentData['casher_id']);
            }

            DB::commit();
            return $payment->load(['paymentMethod', 'casher', 'sale']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Crear venta con pagos múltiples
     */
    public function createSaleWithPayments(array $saleData, array $payments)
    {
        DB::beginTransaction();
        try {
            // Validar que los pagos sumen el total de la venta
            $paymentsTotal = collect($payments)->sum('payment_amount');

            if (abs($paymentsTotal - $saleData['sale_total']) > 0.01) {
                throw new Exception("La suma de los pagos ($paymentsTotal) no coincide con el total de la venta ({$saleData['sale_total']})");
            }

            // Crear la venta
            $sale = SalesHeader::create($saleData);

            // Registrar los pagos
            $saldoActual = $saleData['sale_total'];
            foreach ($payments as $paymentData) {
                $saldoActual -= $paymentData['payment_amount'];

                SalePaymentDetail::create([
                    'sale_id' => $sale->id,
                    'payment_method_id' => $paymentData['payment_method_id'],
                    'casher_id' => $paymentData['casher_id'] ?? auth()->id(),
                    'payment_amount' => $paymentData['payment_amount'],
                    'actual_balance' => $saldoActual,
                    'bank_account_id' => $paymentData['bank_account_id'] ?? null,
                    'reference' => $paymentData['reference'] ?? null,
                    'is_active' => true,
                ]);

                // Registrar movimiento de caja si es efectivo
                $paymentMethod = \App\Models\PaymentMethod::find($paymentData['payment_method_id']);
                if ($paymentMethod && $paymentMethod->code === '01') {
                    $this->registerCashMovement($sale, $paymentData['payment_amount'], $paymentData['casher_id'] ?? auth()->id());
                }
            }

            // Actualizar estado de pago
            $paymentStatus = $this->calculatePaymentStatus($saleData['sale_total'], $paymentsTotal);
            $sale->update([
                'pending_balance' => $saldoActual,
                'payment_status' => $paymentStatus
            ]);

            DB::commit();
            return $sale->load(['paymentDetails.paymentMethod', 'customer', 'warehouse', 'seller']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calcular estado de pago
     * 1 = Pendiente
     * 2 = Pago Parcial
     * 3 = Pagado
     */
    private function calculatePaymentStatus($total, $paid)
    {
        if ($paid == 0) {
            return 1; // Pendiente
        } elseif ($paid < $total) {
            return 2; // Pago Parcial
        } else {
            return 3; // Pagado
        }
    }

    /**
     * Registrar movimiento de caja por pago en efectivo
     */
    private function registerCashMovement($sale, $amount, $userId)
    {
        // Buscar la apertura de caja activa
        if (!$sale->cashbox_open_id) {
            return; // No hay caja asociada
        }

        CashMovement::create([
            'cash_opening_id' => $sale->cashbox_open_id,
            'user_id' => $userId,
            'type' => 'income',
            'amount' => $amount,
            'concept' => 'Venta - Pago en efectivo',
            'description' => "Pago de venta #{$sale->id}",
            'reference' => "SALE-{$sale->id}",
            'sale_id' => $sale->id,
            'movement_date' => Carbon::now(),
        ]);
    }

    /**
     * Obtener historial de pagos de una venta
     */
    public function getPaymentHistory(int $saleId)
    {
        $sale = SalesHeader::with(['paymentDetails.paymentMethod', 'paymentDetails.casher'])
            ->findOrFail($saleId);

        $totalPaid = $sale->paymentDetails->sum('payment_amount');
        $pendingBalance = $sale->sale_total - $totalPaid;

        return [
            'sale' => $sale,
            'total_paid' => $totalPaid,
            'pending_balance' => $pendingBalance,
            'payment_status' => $this->calculatePaymentStatus($sale->sale_total, $totalPaid),
            'payments' => $sale->paymentDetails,
        ];
    }

    /**
     * Obtener ventas pendientes de pago (cuentas por cobrar)
     */
    public function getAccountsReceivable($filters = [])
    {
        $query = SalesHeader::with(['customer', 'warehouse', 'seller', 'paymentDetails'])
            ->where('payment_status', '!=', 3); // No incluir ventas completamente pagadas

        // Filtros
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['overdue_only']) && $filters['overdue_only']) {
            $query->where('due_date', '<', Carbon::now())
                ->whereNotNull('due_date');
        }

        $sales = $query->orderBy('due_date', 'asc')->get();

        // Calcular saldos
        $sales->each(function ($sale) {
            $totalPaid = $sale->paymentDetails->sum('payment_amount');
            $sale->total_paid = $totalPaid;
            $sale->current_balance = $sale->sale_total - $totalPaid;
        });

        return $sales;
    }
}
