<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\JobsTitleStoreRequest;
use App\Http\Requests\Api\v1\JobsTitleUpdateRequest;
use App\Http\Resources\Api\v1\JobsTitleCollection;
use App\Http\Resources\Api\v1\JobsTitleResource;
use App\Models\JobsTitle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JobsTitleController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $jobsTitles = JobsTitle::paginate($perPage);
            return ApiResponse::success(new JobsTitleCollection($jobsTitles), 'Cargos laborales recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(JobsTitleStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitle = (new \App\Models\JobsTitle)->create($request->validated());
            return ApiResponse::success(new JobsTitleResource($jobsTitle), 'Cargo laboral creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitle = (new \App\Models\JobsTitle)->findOrFail($id);
            return ApiResponse::success(new JobsTitleResource($jobsTitle), 'Cargo laboral recuperado', 200);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Cargo laboral no encontrado', 'Cargo laboral no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);

        }
    }

    public function update(JobsTitleUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitle = (new \App\Models\JobsTitle)->findOrFail($id);
            $jobsTitle->update($request->validated());
            return ApiResponse::success(new JobsTitleResource($jobsTitle), 'Cargo laboral actualizado', 200);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Cargo laboral no encontrado', 'Cargo laboral no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitle = (new \App\Models\JobsTitle)->findOrFail($id);
            $jobsTitle->delete();
            return ApiResponse::success(null, 'Cargo laboral eliminado', 204);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Cargo laboral no encontrado', 'Cargo laboral no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
