<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\HistoryDteStoreRequest;
use App\Http\Requests\Api\v1\HistoryDteUpdateRequest;
use App\Http\Resources\Api\v1\HistoryDteCollection;
use App\Http\Resources\Api\v1\HistoryDteResource;
use App\Models\HistoryDte;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HistoryDteController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $query = HistoryDte::with(['salesHeader.customer']);

            // Filtros
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('codigo_generacion', 'like', "%{$search}%")
                      ->orWhere('numero_control', 'like', "%{$search}%")
                      ->orWhereHas('salesHeader.customer', function($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('date_from')) {
                $query->whereDate('fecha_emision', '>=', $request->input('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('fecha_emision', '<=', $request->input('date_to'));
            }

            if ($request->has('ambiente')) {
                $query->where('ambiente', $request->input('ambiente'));
            }

            $historyDtes = $query->orderBy('fecha_emision', 'desc')->paginate($perPage);
           return ApiResponse::success($historyDtes,'Historial de DTEs recuperado', 200);

        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(),500);
        }
    }

    /**
     * Obtener estadísticas de DTEs
     */
    public function getStats(): \Illuminate\Http\JsonResponse
    {
        try {
            $stats = [
                'total' => HistoryDte::count(),
                'aprobados' => HistoryDte::where('status', 'APROBADO')->count(),
                'rechazados' => HistoryDte::where('status', 'RECHAZADO')->count(),
                'observados' => HistoryDte::where('status', 'OBSERVADO')->count(),
                'pendientes' => HistoryDte::where('status', 'PENDIENTE')->count(),
                'total_facturado' => HistoryDte::where('status', 'APROBADO')->sum('total'),
                'ultimo_mes' => HistoryDte::whereMonth('fecha_emision', now()->month)
                                          ->whereYear('fecha_emision', now()->year)
                                          ->count(),
            ];

            return ApiResponse::success($stats, 'Estadísticas de DTEs', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    public function store(HistoryDteStoreRequest $request): Response
    {
        $historyDte = HistoryDte::create($request->validated());

        return new HistoryDteResource($historyDte);
    }

    public function show(Request $request, HistoryDte $historyDte): Response
    {
        return new HistoryDteResource($historyDte);
    }

    public function update(HistoryDteUpdateRequest $request, HistoryDte $historyDte): Response
    {
        $historyDte->update($request->validated());

        return new HistoryDteResource($historyDte);
    }

    public function destroy(Request $request, HistoryDte $historyDte): Response
    {
        $historyDte->delete();

        return response()->noContent();
    }
}
