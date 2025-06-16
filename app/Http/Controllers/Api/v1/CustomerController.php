<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerStoreRequest;
use App\Http\Requests\Api\v1\CustomerUpdateRequest;
use App\Http\Resources\Api\v1\CustomerCollection;
use App\Http\Resources\Api\v1\CustomerResource;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $customers = Customer::with('documentType','warehouse','departamento','municipio')->paginate($perPage);
            return ApiResponse::success($customers, 'Clientes recuperados cone éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(CustomerStoreRequest $request): JsonResponse
    {
        try {
            $customer = (new Customer)->create($request->validated());
            return ApiResponse::success($customer, 'Cliente almacenado de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $customer = Customer::with('address')->findOrFail($id);
            return ApiResponse::success($customer, 'Cliente recuperado con éxito', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No existe el cliente', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(CustomerUpdateRequest $request, $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->update($request->validated());

            return ApiResponse::success($customer, 'Cliente recuperado con éxito', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No existe el cliente', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, Customer $customer): Response
    {
        $customer->delete();

        return response()->noContent();
    }
}