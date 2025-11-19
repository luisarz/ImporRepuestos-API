<?php

namespace App\Services;

use App\Models\PurchasesHeader;
use App\Models\PurchasePaymentDetail;
use App\Models\CashMovement;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchasesService
{
    /**
     * Registrar un pago/abono para una compra a crédito
     */
    public function registerPayment(int $purchaseId, array $paymentData)
    {
        DB::beginTransaction();
        try {
            $purchase = PurchasesHeader::with('paymentDetails')->findOrFail($purchaseId);

            // Validar que la compra permita pagos (no debe estar cancelada)
            if ($purchase->status_purchase == '3') { // 3 = Cancelada
                throw new Exception('No se pueden registrar pagos en una compra cancelada');
            }

            // Usar pending_balance si existe, si no, calcularlo
            $actualBalance = $purchase->pending_balance ?? ($purchase->total_purchase - $purchase->paymentDetails->sum('payment_amount'));

            // Validar que el pago no exceda el saldo
            if ($paymentData['payment_amount'] > $actualBalance) {
                throw new Exception("El monto del pago ($" . $paymentData['payment_amount'] . ") excede el saldo pendiente ($" . $actualBalance . ")");
            }

            // Calcular nuevo saldo
            $newBalance = $actualBalance - $paymentData['payment_amount'];

            // Crear el registro de pago
            $payment = PurchasePaymentDetail::create([
                'purchase_id' => $purchaseId,
                'payment_method_id' => $paymentData['payment_method_id'],
                'casher_id' => $paymentData['casher_id'],
                'payment_amount' => $paymentData['payment_amount'],
                'actual_balance' => $newBalance,
                'bank_account_id' => $paymentData['bank_account_id'] ?? null,
                'reference' => $paymentData['reference'] ?? null,
                'is_active' => true,
            ]);

            // Actualizar saldo pendiente y estado de pago de la compra
            $purchase->pending_balance = $newBalance;

            if ($newBalance <= 0) {
                $purchase->payment_status = '2'; // Pagada
            } else {
                $purchase->payment_status = '1'; // Parcial
            }
            $purchase->save();

            // Registrar movimiento de caja si hay caja abierta
            if (isset($paymentData['cash_opening_id'])) {
                CashMovement::create([
                    'cash_opening_id' => $paymentData['cash_opening_id'],
                    'movement_type' => 2, // 2 = Egreso
                    'description' => "Pago a compra #{$purchase->purchase_number}",
                    'amount' => $paymentData['payment_amount'],
                    'payment_method_id' => $paymentData['payment_method_id'],
                    'reference_type' => 'purchase_payment',
                    'reference_id' => $payment->id,
                    'employee_id' => $paymentData['casher_id'],
                ]);
            }

            DB::commit();
            return $payment->load(['paymentMethod', 'casher']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener historial de pagos de una compra
     */
    public function getPaymentHistory(int $purchaseId)
    {
        $purchase = PurchasesHeader::with([
            'paymentDetails' => function($q) {
                $q->with(['paymentMethod', 'casher'])
                  ->orderBy('created_at', 'desc');
            },
            'provider'
        ])->findOrFail($purchaseId);

        $totalPaid = $purchase->paymentDetails->sum('payment_amount');
        $balance = $purchase->total_purchase - $totalPaid;

        return [
            'purchase' => $purchase,
            'total_paid' => $totalPaid,
            'balance' => $balance,
            'payments' => $purchase->paymentDetails
        ];
    }

    /**
     * Obtener cuentas por pagar (compras pendientes de pago)
     */
    public function getAccountsPayable($filters = [])
    {
        $query = PurchasesHeader::with(['provider', 'warehouse', 'employee', 'paymentDetails', 'operationCondition'])
            ->where('operation_condition_id', '2') // Solo compras a crédito
            ->where('status_purchase', '2') // Solo compras finalizadas
            ->where('pending_balance', '>', 0); // Solo con saldo pendiente

        // Filtros
        if (isset($filters['provider_id']) && !empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['warehouse_id']) && !empty($filters['warehouse_id'])) {
            $query->where('warehouse', $filters['warehouse_id']);
        }

        if (isset($filters['overdue_only']) && $filters['overdue_only'] === 'true') {
            $query->where('due_date', '<', Carbon::now())
                ->whereNotNull('due_date');
        }

        $purchases = $query->orderBy('due_date', 'asc')->get();

        // Calcular saldos
        $purchases->each(function ($purchase) {
            $totalPaid = $purchase->paymentDetails->sum('payment_amount');
            $purchase->total_paid = $totalPaid;
            // Usar pending_balance si existe, si no calcularlo
            $purchase->current_balance = $purchase->pending_balance ?? ($purchase->total_purchase - $totalPaid);

            // Calcular días de atraso
            if ($purchase->due_date) {
                $dueDate = Carbon::parse($purchase->due_date);
                $purchase->days_overdue = $dueDate->isPast()
                    ? $dueDate->diffInDays(Carbon::now())
                    : 0;
            } else {
                $purchase->days_overdue = 0;
            }
        });

        return $purchases;
    }

    /**
     * Obtener estadísticas de cuentas por pagar
     */
    public function getAccountsPayableStats()
    {
        $pendingPurchases = PurchasesHeader::with('paymentDetails')
            ->where('operation_condition_id', '2') // Solo compras a crédito
            ->whereIn('payment_status', ['0', '1']) // Solo pendientes (0) o parciales (1)
            ->where('status_purchase', '!=', '3')
            ->get();

        $totalDebt = 0;
        $totalPurchases = 0;
        $totalPaid = 0;

        foreach ($pendingPurchases as $purchase) {
            $paid = $purchase->paymentDetails->sum('payment_amount');
            $totalPurchases += $purchase->total_purchase;
            $totalPaid += $paid;
            $totalDebt += ($purchase->total_purchase - $paid);
        }

        $overdue = PurchasesHeader::where('operation_condition_id', '2')
            ->where('payment_status', '!=', '2')
            ->where('status_purchase', '!=', '3')
            ->where('due_date', '<', Carbon::now())
            ->whereNotNull('due_date')
            ->count();

        return [
            'total_debt' => $totalDebt,
            'total_purchases' => $totalPurchases,
            'total_paid' => $totalPaid,
            'overdue_count' => $overdue
        ];
    }
}
