<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Services\CashService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashMovementController extends Controller
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
            $typeFilter = $request->input('type_filter', '');
            $openingFilter = $request->input('opening_filter', '');
            $sortBy = $request->input('sortField', 'movement_date');
            $sortOrderRaw = $request->input('sortOrder', 'desc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $query = CashMovement::query()->with(['cashOpening.cashRegister', 'user', 'sale']);

            // Búsqueda
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('concept', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('reference', 'like', "%{$search}%");
                });
            }

            // Filtros
            if ($typeFilter !== '') {
                $query->where('type', $typeFilter);
            }

            if ($openingFilter !== '') {
                $query->where('cash_opening_id', $openingFilter);
            }

            // Ordenamiento
            $allowedSortFields = ['id', 'type', 'amount', 'movement_date', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $movements = $query->paginate($perPage);
            return ApiResponse::success($movements, 'Lista de movimientos de caja', 200);
        } catch (Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Registrar un movimiento
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cash_opening_id' => 'required|exists:cash_openings,id',
                'user_id' => 'required|exists:users,id',
                'type' => 'required|in:income,expense',
                'amount' => 'required|numeric|min:0.01',
                'concept' => 'required|string|max:200',
                'description' => 'nullable|string',
                'reference' => 'nullable|string|max:100',
                'sale_id' => 'nullable|exists:sales_headers,id',
                'movement_date' => 'nullable|date',
            ]);

            $movement = $this->cashService->registerMovement($validated);

            return ApiResponse::success($movement, 'Movimiento registrado exitosamente', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al registrar el movimiento', 400);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $movement = CashMovement::with([
                'cashOpening.cashRegister',
                'user',
                'sale'
            ])->findOrFail($id);

            return ApiResponse::success($movement, 'Detalle del movimiento', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Movimiento no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener el movimiento', 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $movement = CashMovement::findOrFail($id);

            // Verificar que la apertura esté abierta
            if ($movement->cashOpening->isClosed()) {
                return ApiResponse::error(null, 'No se pueden editar movimientos de una caja cerrada', 400);
            }

            $validated = $request->validate([
                'type' => 'sometimes|in:income,expense',
                'amount' => 'sometimes|numeric|min:0.01',
                'concept' => 'sometimes|string|max:200',
                'description' => 'nullable|string',
                'reference' => 'nullable|string|max:100',
            ]);

            $movement->update($validated);
            $movement->load(['cashOpening', 'user', 'sale']);

            return ApiResponse::success($movement, 'Movimiento actualizado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Movimiento no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al actualizar el movimiento', 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $movement = CashMovement::findOrFail($id);

            // Verificar que la apertura esté abierta
            if ($movement->cashOpening->isClosed()) {
                return ApiResponse::error(null, 'No se pueden eliminar movimientos de una caja cerrada', 400);
            }

            // No permitir eliminar movimientos asociados a ventas
            if ($movement->sale_id) {
                return ApiResponse::error(null, 'No se pueden eliminar movimientos asociados a ventas', 400);
            }

            $movement->delete();
            return ApiResponse::success(null, 'Movimiento eliminado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Movimiento no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el movimiento', 400);
        }
    }

    /**
     * Obtener movimientos por apertura
     */
    public function getByOpening($openingId): JsonResponse
    {
        try {
            $movements = CashMovement::where('cash_opening_id', $openingId)
                ->with(['user', 'sale'])
                ->orderBy('movement_date', 'desc')
                ->get();

            return ApiResponse::success($movements, 'Movimientos obtenidos exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener los movimientos', 500);
        }
    }

    /**
     * Estadísticas de movimientos
     */
    public function stats(): JsonResponse
    {
        try {
            $totalIncomes = CashMovement::where('type', 'income')->sum('amount');
            $totalExpenses = CashMovement::where('type', 'expense')->sum('amount');
            $countIncomes = CashMovement::where('type', 'income')->count();
            $countExpenses = CashMovement::where('type', 'expense')->count();
            $todayMovements = CashMovement::whereDate('movement_date', today())->count();

            $stats = [
                'total_incomes' => (float) $totalIncomes,
                'total_expenses' => (float) $totalExpenses,
                'count_incomes' => $countIncomes,
                'count_expenses' => $countExpenses,
                'today_movements' => $todayMovements,
                'balance' => (float) ($totalIncomes - $totalExpenses),
            ];

            return ApiResponse::success($stats, 'Estadísticas obtenidas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener estadísticas', 500);
        }
    }
}
