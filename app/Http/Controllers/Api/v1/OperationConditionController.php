<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\OperationConditionStoreRequest;
use App\Http\Requests\Api\v1\OperationConditionUpdateRequest;
use App\Http\Resources\Api\v1\OperationConditionCollection;
use App\Http\Resources\Api\v1\OperationConditionResource;
use App\Models\OperationCondition;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OperationConditionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $operationConditions = OperationCondition::where('name', 'like', "%$search%")->paginate($perPage);
            return ApiResponse::success($operationConditions, 'Operation conditions retrieved successfully', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while retrieving the operation conditions', 500);
        }

    }

    public function store(OperationConditionStoreRequest $request): JsonResponse
    {
        try {
            $operationCondition = OperationCondition::create($request->validated());
            return ApiResponse::success($operationCondition, 'Operation condition created successfully', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while creating the operation condition', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $operationCondition = OperationCondition::findOrFail($id);
            return ApiResponse::success($operationCondition, 'Operation condition retrieved successfully', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Operation condition not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while retrieving the operation condition', 500);

        }
    }

    public function update(OperationConditionUpdateRequest $request, $id): JsonResponse
    {
        try {
            $operationCondition = (new \App\Models\OperationCondition)->findOrFail($id);
            $operationCondition->update($request->validated());
            return ApiResponse::success($operationCondition, 'Operation condition updated successfully', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while updating the operation condition', 500);
        }

    }

    public function destroy(Request $request, OperationCondition $operationCondition): Response
    {
        $operationCondition->delete();

        return response()->noContent();
    }
}
