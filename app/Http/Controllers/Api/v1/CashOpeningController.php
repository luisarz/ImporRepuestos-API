<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CashOpening;
use App\Services\CashService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashOpeningController extends Controller
{
    protected $cashService;

    public function __construct(CashService $cashService)
    {
        $this->cashService = $cashService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            $cashRegisterFilter = $request->input('cash_register_filter', '');
            $userFilter = $request->input('user_filter', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'desc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $query = CashOpening::query()->with(['cashRegister.warehouse', 'user']);

            // Búsqueda
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('opening_notes', 'like', "%{$search}%")
                      ->orWhere('closing_notes', 'like', "%{$search}%")
                      ->orWhereHas('cashRegister', function($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                      })
                      ->orWhereHas('user', function($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Filtros
            if ($statusFilter !== '') {
                $query->where('status', $statusFilter);
            }

            if ($cashRegisterFilter !== '') {
                $query->where('cash_register_id', $cashRegisterFilter);
            }

            if ($userFilter !== '') {
                $query->where('user_id', $userFilter);
            }

            // Ordenamiento
            $allowedSortFields = ['id', 'cash_register_id', 'user_id', 'opened_at', 'closed_at', 'status', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $openings = $query->paginate($perPage);
            return ApiResponse::success($openings, 'Lista de aperturas de caja', 200);
        } catch (Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Abrir una caja
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cash_register_id' => 'required|exists:cash_registers,id',
                'user_id' => 'required|exists:users,id',
                'opening_amount' => 'required|numeric|min:0',
                'opening_notes' => 'nullable|string',
            ]);

            $opening = $this->cashService->openCash($validated);

            return ApiResponse::success($opening, 'Caja abierta exitosamente', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al abrir la caja', 400);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $opening = CashOpening::with([
                'cashRegister.warehouse',
                'user',
                'cashMovements.user',
                'cashMovements.sale'
            ])->findOrFail($id);

            // Agregar resumen de movimientos
            $summary = $opening->getMovementsSummary();
            $expectedAmount = $opening->calculateExpectedAmount();

            $result = [
                'opening' => $opening,
                'summary' => $summary,
                'expected_amount' => $expectedAmount,
            ];

            return ApiResponse::success($result, 'Detalle de la apertura de caja', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Apertura de caja no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener la apertura', 500);
        }
    }

    /**
     * Cerrar una caja
     */
    public function close(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'closing_amount' => 'required|numeric|min:0',
                'closing_notes' => 'nullable|string',
            ]);

            $opening = $this->cashService->closeCash($id, $validated);

            return ApiResponse::success($opening, 'Caja cerrada exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cerrar la caja', 400);
        }
    }

    /**
     * Obtener la apertura actual de un usuario
     */
    public function getCurrentByUser(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return ApiResponse::error(null, 'El ID de usuario es requerido', 400);
            }

            $opening = $this->cashService->getUserOpenCash($userId);

            if (!$opening) {
                return ApiResponse::success(null, 'El usuario no tiene una caja abierta', 200);
            }

            return ApiResponse::success($opening, 'Apertura actual del usuario', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener la apertura actual', 500);
        }
    }

    /**
     * Obtener reporte de una apertura
     */
    public function report($id): JsonResponse
    {
        try {
            $report = $this->cashService->getCashReport($id);
            return ApiResponse::success($report, 'Reporte de caja generado exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Obtener estadísticas por rango de fechas
     */
    public function statsByDateRange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $stats = $this->cashService->getCashStatsByDateRange(
                $validated['warehouse_id'],
                $validated['start_date'],
                $validated['end_date']
            );

            return ApiResponse::success($stats, 'Estadísticas obtenidas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener las estadísticas', 500);
        }
    }

    /**
     * Estadísticas generales
     */
    public function stats(): JsonResponse
    {
        try {
            $total = CashOpening::count();
            $open = CashOpening::where('status', 'open')->count();
            $closed = CashOpening::where('status', 'closed')->count();
            $todayOpenings = CashOpening::whereDate('opened_at', today())->count();

            $stats = [
                'total' => $total,
                'open' => $open,
                'closed' => $closed,
                'today_openings' => $todayOpenings,
            ];

            return ApiResponse::success($stats, 'Estadísticas obtenidas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener estadísticas', 500);
        }
    }
}
