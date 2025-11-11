<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CompanyStoreRequest;
use App\Http\Requests\Api\v1\CompanyUpdateRequest;
use App\Http\Resources\Api\v1\CompanyCollection;
use App\Http\Resources\Api\v1\CompanyResource;
use App\Models\Company;
use App\Models\District;
use App\Models\EconomicActivity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $companies = Company::paginate($perPage);
            return ApiResponse::success($companies,'Configuración de empresa recuperada',200);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(),500);
        }

    }

    public function store(CompanyStoreRequest $request): CompanyResource
    {
        $company = (new Company)->create($request->validated());
        return new CompanyResource($company);
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $company=Company::findOrFail($id);
            $district=District::all();
            $economic_activity=EconomicActivity::all();
            $response=[
                'company'=>$company,
                'district'=>$district,
                'economic_activity'=>$economic_activity
            ];
            return ApiResponse::success($response,'Empresa recuperada de manera exitosa',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Empresa no encontrada',404);
        }catch(\Exception $e){
            return ApiResponse::error(null,$e->getMessage(),500);
        }
    }

    public function update(CompanyUpdateRequest $request, $id): JsonResponse
    {
        try {
            $company = Company::findOrFail($id);
            $data = $request->validated();

            // Manejar la carga de logo
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior si existe
                if ($company->logo && isset($company->logo['path'])) {
                    \Storage::disk('public')->delete($company->logo['path']);
                }

                $file = $request->file('logo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('companies/logos', $filename, 'public');

                $data['logo'] = [
                    'url' => asset('storage/' . $path),
                    'path' => $path,
                    'filename' => $filename
                ];
            }

            $company->update($data);
            return ApiResponse::success($company, 'Empresa actualizada de manera exitosa', 200);
        }catch (ModelNotFoundException $exception){
            return ApiResponse::error(null, 'Empresa no encontrada', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Empresa no actualizada', 400);
        }

    }

    /**
     * Obtener estadísticas de empresas
     */
    public function stats(): JsonResponse
    {
        try {
            $total = Company::count();
            $active = Company::where('is_active', 1)->count();
            $inactive = Company::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $company = Company::find($id);
            if (!$company) {
                return ApiResponse::error(null, 'Empresa no encontrada', 404);
            }
            $company->delete();
            return ApiResponse::success(null, 'Empresa eliminada de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Empresa no eliminada', 400);
        }
    }
}
