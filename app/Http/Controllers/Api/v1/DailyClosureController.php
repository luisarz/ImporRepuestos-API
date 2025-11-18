<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\CashService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyClosureController extends Controller
{
    protected $cashService;

    public function __construct(CashService $cashService)
    {
        $this->cashService = $cashService;
    }

    /**
     * Obtener reporte detallado para cierre diario
     * GET /api/v1/daily-closure/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $report = $this->cashService->getDetailedCashReport($id);
            return ApiResponse::success($report, 'Reporte detallado generado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Cerrar caja con conteo de denominaciones
     * POST /api/v1/daily-closure/{id}/close
     */
    public function closeWithDenominations(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'closing_amount' => 'required|numeric|min:0',
                'denominations' => 'nullable|array',
                'denominations.*.value' => 'required_with:denominations|numeric|min:0',
                'denominations.*.quantity' => 'required_with:denominations|integer|min:0',
                'denominations.*.type' => 'required_with:denominations|in:bill,coin',
                'closing_notes' => 'nullable|string|max:1000',
                'authorization_notes' => 'nullable|string|max:1000',
                'authorized_by' => 'nullable|exists:users,id',
                'tolerance' => 'nullable|numeric|min:0'
            ]);

            $closure = $this->cashService->closeWithDenominations($id, [
                ...$validated,
                'user_id' => auth()->id()
            ]);

            return ApiResponse::success($closure, 'Cierre de caja realizado exitosamente', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Generar PDF del cierre
     * GET /api/v1/daily-closure/{id}/pdf
     */
    public function generatePDF($id)
    {
        try {
            $report = $this->cashService->getDetailedCashReport($id);

            // Verificar que la caja esté cerrada
            if ($report['opening']->status !== 'closed') {
                return ApiResponse::error(null, 'Solo se puede generar PDF de cajas cerradas', 400);
            }

            // Generar PDF con opciones específicas
            $pdf = Pdf::loadView('pdfs.cash-closure', $report);

            // Configurar opciones de dompdf
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'sans-serif',
                'dpi' => 96,
                'enable_php' => false,
            ]);

            // Configurar tamaño de papel (letter = 8.5" x 11")
            $pdf->setPaper('letter', 'portrait');

            $filename = 'cierre-caja-' . $report['opening']->id . '-' . date('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Cierre de caja no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Obtener lista de supervisores para autorización
     * GET /api/v1/daily-closure/supervisors
     */
    public function getSupervisors(): JsonResponse
    {
        try {
            // Obtener todos los usuarios activos
            // TODO: Filtrar por rol cuando esté configurado correctamente
            $supervisors = \App\Models\User::where('is_active', 1)
                ->select('id', 'name', 'email')
                ->get();

            return ApiResponse::success($supervisors, 'Supervisores recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }
}
