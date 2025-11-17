<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\BatchStoreRequest;
use App\Http\Requests\Api\v1\BatchUpdateRequest;
use App\Http\Resources\Api\v1\BatchCollection;
use App\Http\Resources\Api\v1\BatchResource;
use App\Models\Batch;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BatchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $batches = Batch::with(['inventory:id,warehouse_id,product_id', 'inventory.product:id,code,original_code,description','inventory.warehouse:id,name','origenCode:id,code'])->paginate($perPage);
            return ApiResponse::success($batches, 'Lotes recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);

        }
    }

    public function store(BatchStoreRequest $request): JsonResponse
    {
        try {
            $batch = (new Batch)->create($request->validated());
            return ApiResponse::success($batch, 'Lote creado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al almacenar el lote', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $batch = Batch::with([
                'inventory:id,warehouse_id,product_id',
                'inventory.product:id,code,original_code,description',
                'inventory.warehouse:id,name',
                'origenCode:id,code,description',
                'purchaseItem:id,purchase_id,product_id,quantity,unit_price',
                'purchaseItem.purchase:id,document_number,purchase_date'
            ])->findOrFail($id);

            return ApiResponse::success($batch, 'Lote recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Lote no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(BatchUpdateRequest $request, $id): JsonResponse
    {
        try {
            $batch = (new \App\Models\Batch)->with(['inventory:id,warehouse_id,product_id', 'inventory.product:id,code,original_code,description'])->findOrFail($id);
            $batch->update($request->validated());
            return ApiResponse::success($batch, 'Lote recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Lote no encontrad', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un erro', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {

        try {
            $batch = (new \App\Models\Batch)->findOrFail($id);
            $batch->delete();
            return ApiResponse::success(null, 'Lote Eliminado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Lote no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un erro', 500);
        }
    }

    /**
     * Obtener estadísticas de lotes
     */
    public function stats(): JsonResponse
    {
        try {
            $total = Batch::count();
            $active = Batch::where('is_active', 1)->count();
            $inactive = Batch::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Generar PDF para imprimir un lote
     */
    public function printPdf($id): Response
    {
        try {
            // Obtener el lote con todas las relaciones necesarias
            $batch = Batch::with([
                'inventory.product',
                'inventory.warehouse',
                'origenCode',
                'purchaseItem.purchase'
            ])->findOrFail($id);

            // Calcular días hasta vencimiento
            $expirationDate = Carbon::parse($batch->expiration_date);
            $now = Carbon::now();
            $daysToExpire = $now->diffInDays($expirationDate, false);
            $isExpired = $daysToExpire < 0;

            // Preparar datos para la vista
            $data = [
                'batch' => $batch,
                'daysToExpire' => abs($daysToExpire),
                'isExpired' => $isExpired,
            ];

            // Generar PDF
            $pdf = Pdf::loadView('batches.batch-print-pdf', $data);
            $pdf->setPaper('letter', 'portrait');

            // Retornar el PDF para abrir en nueva ventana
            return $pdf->stream("Lote-{$batch->code}.pdf");

        } catch (ModelNotFoundException $e) {
            abort(404, 'Lote no encontrado');
        } catch (\Exception $e) {
            abort(500, 'Error al generar PDF: ' . $e->getMessage());
        }
    }
}
