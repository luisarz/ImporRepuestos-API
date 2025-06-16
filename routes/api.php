<?php


use App\Http\Requests\MenuAllowedRequest;
use App\Http\Controllers\Api\v1\{ApplicationController,
    BatchCodeOrigenController,
    DocumentTypeController,
    InterchangesController,
    MenuAllowedController,
    ModuleRolController,
    ModuloController,
    OperationConditionController,
    PaymentMethodController,
    RolesController,
    UserController};
use App\Http\Controllers\Api\v1\BatchController;
use App\Http\Controllers\Api\v1\BrandController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\CompanyController;
use App\Http\Controllers\Api\v1\CountryController;
use App\Http\Controllers\Api\v1\CustomerAddressCatalogController;
use App\Http\Controllers\Api\v1\CustomerController;
use App\Http\Controllers\Api\v1\CustomerDocumentsTypeController;
use App\Http\Controllers\Api\v1\CustomerTypeController;
use App\Http\Controllers\Api\v1\DepartmentController;
use App\Http\Controllers\Api\v1\DistrictController;
use App\Http\Controllers\Api\v1\DocumentsTypesProviderController;
use App\Http\Controllers\Api\v1\EconomicActivityController;
use App\Http\Controllers\Api\v1\EmployeeController;
use App\Http\Controllers\Api\v1\EquivalentController;
use App\Http\Controllers\Api\v1\FuelTypeController;
use App\Http\Controllers\Api\v1\HistoryDteController;
use App\Http\Controllers\Api\v1\InventoriesBatchController;
use App\Http\Controllers\Api\v1\InventoryController;
use App\Http\Controllers\Api\v1\JobsTitleController;
use App\Http\Controllers\Api\v1\MunicipalityController;
use App\Http\Controllers\Api\v1\PlateTypeController;
use App\Http\Controllers\Api\v1\PriceController;
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\ProviderAddressCatalogController;
//use App\Http\Controllers\Api\v1\ProviderAddressController;
use App\Http\Controllers\Api\v1\ProviderController;
use App\Http\Controllers\Api\v1\ProvidersTypeController;
use App\Http\Controllers\Api\v1\PurchaseItemController;
use App\Http\Controllers\Api\v1\PurchasesHeaderController;
use App\Http\Controllers\Api\v1\QuotePurchaseController;
use App\Http\Controllers\Api\v1\QuotePurchaseItemController;
use App\Http\Controllers\Api\v1\SaleItemController;
use App\Http\Controllers\Api\v1\SalePaymentDetailController;
use App\Http\Controllers\Api\v1\SalesDteController;
use App\Http\Controllers\Api\v1\SalesHeaderController;
use App\Http\Controllers\Api\v1\StablishmentTypeController;
use App\Http\Controllers\Api\v1\UnitMeasurementController;
use App\Http\Controllers\Api\v1\VehicleController;
use App\Http\Controllers\Api\v1\VehicleModelController;
use App\Http\Controllers\Api\v1\WarehouseController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\DTEController;
use App\Http\Controllers\Api\v1\SenEmailDTEController;



Route::post('login', [LoginController::class, 'login']);

Route::middleware(['jwt'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout']);

    Route::get('menu',[MenuAllowedController::class,'index']);

    Route::apiResource('roles', RolesController::class);
    Route::apiResource('modulos', ModuloController::class);
    Route::get('modulos-all', [ModuloController::class, 'getAll']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('permissions', ModuleRolController::class);
//    Route::post('permissions/allow-access', [ModuleRolController::class, 'allowAccess']);


    #Hacienda Catalogs (Países, Departamentos, Municipios, Distritos)

    Route::apiResource('countries', CountryController::class);

    Route::apiResource('operation-conditions', OperationConditionController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('municipalities', MunicipalityController::class);
    Route::apiResource('districts', DistrictController::class);


    #Empresa sucursal y almacenes (Actividades Económicas, Empresas, Tipos de Establecimientos, Almacenes)
    Route::apiResource('economic-activities', EconomicActivityController::class);
    Route::apiResource('company', CompanyController::class);
    Route::apiResource('establishment-types', StablishmentTypeController::class);
    Route::apiResource('warehouses', WarehouseController::class);

    #Proveedores
    Route::apiResource('providers-types', ProvidersTypeController::class);
    Route::apiResource('providers-documents-types', DocumentsTypesProviderController::class);
    Route::apiResource('providers', ProviderController::class);
    Route::apiResource('provider-address-catalogs', ProviderAddressCatalogController::class);

    #Empleados (Cargos, Empleados)
    Route::apiResource('jobs-titles', JobsTitleController::class);
    Route::apiResource('employees', EmployeeController::class);

    #Productos
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('unit-measurements', UnitMeasurementController::class);

    #Inventarios
    Route::apiResource('products', ProductController::class);
    Route::post('/v1/products/{id}', [ProductController::class, 'update']);

    Route::get('prices/inventory/{idInventory}', [InventoryController::class, 'getPrices']);
    Route::apiResource('inventories', InventoryController::class);
    Route::apiResource('prices', PriceController::class);

    #vehiculos
    Route::apiResource('plate-types', PlateTypeController::class);
    Route::apiResource('vehicle-models', VehicleModelController::class);
    Route::apiResource('fuel-types', FuelTypeController::class);
    Route::apiResource('vehicles', VehicleController::class);

    #Aplicaciones y equivalencias
    Route::apiResource('applications', ApplicationController::class);
    Route::apiResource('equivalents', EquivalentController::class);
    Route::get('equivalents/product/{id_products}', [EquivalentController::class, 'getEquivalentByProduct']);

    Route::apiResource('interchanges', InterchangesController::class);
    Route::get('interchanges/product/{id_product}', [InterchangesController::class, 'getInterchangeByProduct']);

    #Purchase Header, Items and batches,
    Route::apiResource('purchases-headers', PurchasesHeaderController::class);
    Route::apiResource('purchase-items', PurchaseItemController::class);

    # Batches individual and batch CODE Cp,CL, J
    Route::apiResource('batch-code-origens', BatchCodeOrigenController::class);
    Route::apiResource('batches', BatchController::class);

    #Se EJECUTARÁ el registro al momento de finalizar el registro de un lote en compras o realizar una venta o traslado
    Route::apiResource('inventories-batches', InventoriesBatchController::class);


    #Customer documents types, customer
    Route::apiResource('customer-documents-types', CustomerDocumentsTypeController::class);
    Route::apiResource('customer-types', CustomerTypeController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('customer-address-catalogs', CustomerAddressCatalogController::class);

    #Sales, Items, DTE, Payment Details
    Route::apiResource('sales', SalesHeaderController::class);
    Route::get('sale-items', [SaleItemController::class, 'index']);
    Route::get('sale-items/{id_venta}', [SaleItemController::class, 'index']);
    Route::get('sale-details/{id_venta}', [SaleItemController::class, 'details']);
    Route::get('sale-total/{id_venta}', [SaleItemController::class, 'totalSale']);
    Route::get('sale-item/{id_item}', [SaleItemController::class, 'show']);
    Route::apiResource('sale-items', SaleItemController::class);

    Route::apiResource('sales-dtes', SalesDteController::class);

    Route::apiResource('sale-payment-details', SalePaymentDetailController::class);

    Route::apiResource('history-dtes', HistoryDteController::class);





    Route::apiResource('quote-purchases', QuotePurchaseController::class);
    Route::apiResource('quote-purchase-items', QuotePurchaseItemController::class);

//    facturacion electronica
    Route::get('/generarDTE/{idVenta}', [DTEController::class, 'generarDTE'])->middleware(['auth'])->name('generarDTE');
    Route::get('/sendAnularDTE/{idVenta}', [DTEController::class, 'anularDTE'])->middleware(['auth'])->name('sendAnularDTE');
    Route::get('/printDTETicket/{idVenta}', [DTEController::class, 'printDTETicket'])->middleware(['auth'])->name('printDTETicket');
    Route::get('/printDTEPdf/{idVenta}', [DTEController::class, 'printDTEPdf'])->middleware(['auth'])->name('printDTEPdf');
    Route::get('/logDTE/{idVenta}', [DTEController::class, 'logDTE'])->middleware(['auth'])->name('logDTE');
    Route::get('/sendDTE/{idVenta}', [SenEmailDTEController::class, 'SenEmailDTEController'])->middleware(['auth'])->name('sendDTE');


    //Catalogos hacienda
    Route::apiResource('document-tax', DocumentTypeController::class);


});


















