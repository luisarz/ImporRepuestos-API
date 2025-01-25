<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\CountryController;



Route::post('register', [LoginController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::middleware(['jwt'])->group(function () {
    Route::get('getuser', [LoginController::class, 'getUser']);

    Route::prefix('users')->group(function (){
        Route::get('/',[UserController::class,'index']);
        Route::get('/{user}',[UserController::class,'show']);
        Route::get('/create',[UserController::class,'create']);
    });

    Route::post('logout', [LoginController::class, 'logout']);



});


Route::get('countries/index', [App\Http\Controllers\CountryController::class, 'Index']);
Route::apiResource('countries', App\Http\Controllers\CountryController::class);


Route::get('countries/index', [App\Http\Controllers\CountryController::class, 'Index']);
Route::apiResource('countries', App\Http\Controllers\CountryController::class);


Route::apiResource('countries', App\Http\Controllers\Api\v1\CountryController::class);


Route::apiResource('countries', App\Http\Controllers\Api\v1\CountryController::class);

Route::apiResource('departments', App\Http\Controllers\Api\v1\DepartmentController::class);

Route::apiResource('providers-types', App\Http\Controllers\Api\v1\ProvidersTypeController::class);

Route::apiResource('municipalities', App\Http\Controllers\Api\v1\MunicipalityController::class);

Route::apiResource('districts', App\Http\Controllers\Api\v1\DistrictController::class);

Route::apiResource('economic-activities', App\Http\Controllers\Api\v1\EconomicActivityController::class);

Route::apiResource('providers', App\Http\Controllers\Api\v1\ProviderController::class);

Route::apiResource('companies', App\Http\Controllers\Api\v1\CompanyController::class);

Route::apiResource('warehouses', App\Http\Controllers\Api\v1\WarehouseController::class);

Route::apiResource('stablishment-types', App\Http\Controllers\Api\v1\StablishmentTypeController::class);

Route::apiResource('jobs-titles', App\Http\Controllers\Api\v1\JobsTitleController::class);

Route::apiResource('employees', App\Http\Controllers\Api\v1\EmployeeController::class);

Route::apiResource('categories', App\Http\Controllers\Api\v1\CategoryController::class);

Route::apiResource('brands', App\Http\Controllers\Api\v1\BrandController::class);

Route::apiResource('products', App\Http\Controllers\Api\v1\ProductController::class);

Route::apiResource('inventories', App\Http\Controllers\Api\v1\InventoryController::class);

Route::apiResource('unit-measurements', App\Http\Controllers\Api\v1\UnitMeasurementController::class);

Route::apiResource('prices', App\Http\Controllers\Api\v1\PriceController::class);

Route::apiResource('vehicles', App\Http\Controllers\Api\v1\VehicleController::class);

Route::apiResource('plate-types', App\Http\Controllers\Api\v1\PlateTypeController::class);

Route::apiResource('applications', App\Http\Controllers\Api\v1\ApplicationController::class);

Route::apiResource('vehicle-models', App\Http\Controllers\Api\v1\VehicleModelController::class);

Route::apiResource('fuel-types', App\Http\Controllers\Api\v1\FuelTypeController::class);

Route::apiResource('documents-types-providers', App\Http\Controllers\Api\v1\DocumentsTypesProviderController::class);

Route::apiResource('provider-address-catalogs', App\Http\Controllers\Api\v1\ProviderAddressCatalogController::class);

Route::apiResource('provider-addresses', App\Http\Controllers\Api\v1\ProviderAddressController::class);

Route::apiResource('equivalents', App\Http\Controllers\Api\v1\EquivalentController::class);

Route::apiResource('purchases-headers', App\Http\Controllers\Api\v1\PurchasesHeaderController::class);

Route::apiResource('batches', App\Http\Controllers\Api\v1\BatchController::class);

Route::apiResource('purchase-items', App\Http\Controllers\Api\v1\PurchaseItemController::class);

Route::apiResource('inventories-batches', App\Http\Controllers\Api\v1\InventoriesBatchController::class);

Route::apiResource('customers', App\Http\Controllers\Api\v1\CustomerController::class);

Route::apiResource('customer-documents-types', App\Http\Controllers\Api\v1\CustomerDocumentsTypeController::class);

Route::apiResource('customer-address-catalogs', App\Http\Controllers\Api\v1\CustomerAddressCatalogController::class);

Route::apiResource('customer-addresses', App\Http\Controllers\Api\v1\CustomerAddressController::class);

Route::apiResource('sales-headers', App\Http\Controllers\Api\v1\SalesHeaderController::class);

Route::apiResource('sales-dtes', App\Http\Controllers\Api\v1\SalesDteController::class);

Route::apiResource('sale-payment-details', App\Http\Controllers\Api\v1\SalePaymentDetailController::class);

Route::apiResource('history-dtes', App\Http\Controllers\Api\v1\HistoryDteController::class);

Route::apiResource('sale-items', App\Http\Controllers\Api\v1\SaleItemController::class);

Route::apiResource('users', App\Http\Controllers\Api\v1\UserController::class);

Route::apiResource('customer-types', App\Http\Controllers\Api\v1\CustomerTypeController::class);

Route::apiResource('batch-code-origens', App\Http\Controllers\Api\v1\BatchCodeOrigenController::class);

Route::apiResource('quote-purchases', App\Http\Controllers\Api\v1\QuotePurchaseController::class);

Route::apiResource('quote-purchase-items', App\Http\Controllers\Api\v1\QuotePurchaseItemController::class);
