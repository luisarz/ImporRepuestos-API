<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CategoryGroupStoreRequest;
use App\Http\Requests\Api\v1\CategoryGroupUpdateRequest;
use App\Http\Resources\Api\v1\CategoryGroupCollection;
use App\Http\Resources\Api\v1\CategoryGroupResource;
use App\Models\CategoryGroup;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $categoryGroups = CategoryGroup::paginate($perPage);
            return ApiResponse::success($categoryGroups,'Category Groups retrieved successfully',200);
        }catch (\Exception $exception){
            return ApiResponse::error($exception->getMessage(),'Error al recuperar los grupos',500);
        }

    }

    public function store(CategoryGroupStoreRequest $request): JsonResponse
    {
        try {
            $categoryGroup = CategoryGroup::create($request->validated());
            return ApiResponse::success($categoryGroup,'Category Group created successfully',201);
        }catch (\Exception $exception){
            return ApiResponse::error($exception->getMessage(),'Error al crear el grupo',500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $categoryGroup=CategoryGroup::findorfail($id);
            return  ApiResponse::success($categoryGroup,'Category Group retrieved successfully',200);

        }catch (ModelNotFoundException $modelNotFoundException ){
            return  ApiResponse::error($modelNotFoundException->getMessage(),'Error al recuperar los grupo',404);
        }
        catch (\Exception $exception){
            return ApiResponse::error($exception->getMessage(),'Error al recuperar los grupo',500);
        }
    }

    public function update(CategoryGroupUpdateRequest $request, $id): JsonResponse
    {
        try {
            $categoryGroup=CategoryGroup::findorfail($id);
            $categoryGroup->update($request->validated());
            return ApiResponse::success($categoryGroup,'Category Group updated successfully',200);
        }catch (\Exception $exception){
            return ApiResponse::error($exception->getMessage(),'Error al actualizar el grupo',500);
        }

    }

    public function destroy(Request $request, CategoryGroup $categoryGroup): JsonResponse
    {
        try {
            $categoryGroup->delete();
            return ApiResponse::success(null,'Category Group deleted successfully',200);
        }catch (\Exception $exception){
            return ApiResponse::error($exception->getMessage(),'Error al eliminar el grupo',500);
        }
    }
}
