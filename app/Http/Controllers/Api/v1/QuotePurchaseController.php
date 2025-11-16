<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\QuotePurchaseStoreRequest;
use App\Http\Requests\Api\v1\QuotePurchaseUpdateRequest;
use App\Http\Resources\Api\v1\QuotePurchaseCollection;
use App\Http\Resources\Api\v1\QuotePurchaseResource;
use App\Models\QuotePurchase;
use App\Models\PurchasesHeader;
use App\Models\PurchaseItem;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class QuotePurchaseController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $quotePurchases = QuotePurchase::paginate($perPage);
            return ApiResponse::success($quotePurchases,'Detalle de ventas', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null,'Ocurrió un error',500);
        }
    }

    public function store(QuotePurchaseStoreRequest $request): Response
    {
        $quotePurchase = QuotePurchase::create($request->validated());

        return new QuotePurchaseResource($quotePurchase);
    }

    public function show(Request $request, QuotePurchase $quotePurchase): Response
    {
        return new QuotePurchaseResource($quotePurchase);
    }

    public function update(QuotePurchaseUpdateRequest $request, QuotePurchase $quotePurchase): Response
    {
        $quotePurchase->update($request->validated());

        return new QuotePurchaseResource($quotePurchase);
    }

    public function destroy(Request $request, QuotePurchase $quotePurchase): Response
    {
        $quotePurchase->delete();

        return response()->noContent();
    }

    /**
     * Convertir cotización a compra
     */
    public function convertToPurchase(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();

            $quote = QuotePurchase::with(['quotePurchaseItems.inventory'])->findOrFail($id);

            // Validar que no haya sido convertida ya
            if ($quote->is_purchased) {
                return ApiResponse::error(null, 'Esta cotización ya fue convertida a compra', 400);
            }

            // Crear encabezado de compra
            $purchase = PurchasesHeader::create([
                'warehouse' => $request->input('warehouse_id'),
                'quote_purchase_id' => $quote->id,
                'provider_id' => $quote->provider,
                'purchase_date' => now()->format('Y-m-d'),
                'serie' => $request->input('serie', 'SERIE'),
                'purchase_number' => $request->input('purchase_number', 'AUTO'),
                'resolution' => $request->input('resolution', 'N/A'),
                'purchase_type' => $request->input('purchase_type', 1),
                'payment_method' => $quote->payment_method,
                'payment_status' => '3', // Pendiente
                'net_amount' => $quote->amount_purchase / 1.13,
                'tax_amount' => ($quote->amount_purchase / 1.13) * 0.13,
                'retention_amount' => 0,
                'total_purchase' => $quote->amount_purchase,
                'employee_id' => $quote->buyer_id,
                'status_purchase' => '1' // Procesando
            ]);

            // Crear items de compra y batches desde items de cotización
            foreach ($quote->quotePurchaseItems as $quoteItem) {
                // Validar que el item tenga inventory_id
                if (!$quoteItem->inventory_id) {
                    throw new \Exception("El item de cotización ID {$quoteItem->id} no tiene inventory_id asignado");
                }

                // Crear purchase item
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'is_purched' => false,
                    'quantity' => $quoteItem->quantity,
                    'price' => $quoteItem->price,
                    'discount' => $quoteItem->discount ?? 0,
                    'total' => $quoteItem->total
                ]);

                // Crear batch asociado al purchase_item
                Batch::create([
                    'purchase_item_id' => $purchaseItem->id,
                    'inventory_id' => $quoteItem->inventory_id,
                    'code' => 'LOTE-' . $purchase->id . '-' . $purchaseItem->id,
                    'origen_code' => $request->input('origen_code', 1), // Default: 1 = COMPRA LOCAL
                    'incoming_date' => now()->format('Y-m-d'),
                    'expiration_date' => $request->input('expiration_date', now()->addYears(2)->format('Y-m-d')),
                    'initial_quantity' => $quoteItem->quantity,
                    'available_quantity' => 0, // Se actualizará cuando se reciba la compra
                    'is_active' => true,
                    'observations' => 'Lote creado desde cotización ID ' . $quote->id
                ]);
            }

            // Marcar cotización como convertida
            $quote->is_purchased = true;
            $quote->save();

            DB::commit();

            return ApiResponse::success($purchase, 'Cotización convertida a compra exitosamente', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al convertir cotización a compra', 500);
        }
    }
}
