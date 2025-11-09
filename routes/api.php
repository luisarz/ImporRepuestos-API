<?php


use App\Http\Requests\MenuAllowedRequest;
use App\Http\Controllers\Api\v1\{ApplicationController,
    BatchCodeOrigenController,
    CategoryGroupController,
    DocumentTypeController,
    InterchangesController,
    MenuController,
    PermissionController,
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
    Route::post('refresh', [LoginController::class, 'refresh']);

    // ========== MENU (NUEVO - Basado en permisos de Spatie) ==========
    Route::prefix('menu')->group(function () {
        Route::get('/', [MenuController::class, 'index']);
        Route::get('/permissions', [MenuController::class, 'permissions']);
    });

    // ========== PERMISSIONS (NUEVO - Gestión de permisos Spatie) ==========
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::get('/grouped', [PermissionController::class, 'groupedByModule']);
        Route::post('/sync', [PermissionController::class, 'syncFromModules']);
    });

    // ========== ROLES ==========
    // Estadísticas y acciones grupales de roles - DEBEN IR ANTES del apiResource
    Route::get('roles/stats/all', [RolesController::class, 'stats']);
    Route::get('roles/all/list', [RolesController::class, 'getAll']);
    Route::post('roles/bulk/get', [RolesController::class, 'bulkGet']);
    Route::post('roles/bulk/activate', [RolesController::class, 'bulkActivate']);
    Route::post('roles/bulk/deactivate', [RolesController::class, 'bulkDeactivate']);
    Route::post('roles/bulk/delete', [RolesController::class, 'bulkDelete']);
    Route::apiResource('roles', RolesController::class);

    // ========== MODULOS ==========
    // Estadísticas y acciones grupales de modulos - DEBEN IR ANTES del apiResource
    Route::get('modulos/stats/all', [ModuloController::class, 'stats']);
    Route::post('modulos/bulk/get', [ModuloController::class, 'bulkGet']);
    Route::post('modulos/bulk/activate', [ModuloController::class, 'bulkActivate']);
    Route::post('modulos/bulk/deactivate', [ModuloController::class, 'bulkDeactivate']);
    Route::post('modulos/bulk/delete', [ModuloController::class, 'bulkDelete']);
    Route::apiResource('modulos', ModuloController::class);
    Route::get('modulos-all', [ModuloController::class, 'getAll']);

    // ========== USERS ==========
    // Estadísticas y acciones grupales de users - DEBEN IR ANTES del apiResource
    Route::get('users/stats/all', [UserController::class, 'stats']);
    Route::post('users/bulk/get', [UserController::class, 'bulkGet']);
    Route::post('users/bulk/activate', [UserController::class, 'bulkActivate']);
    Route::post('users/bulk/deactivate', [UserController::class, 'bulkDeactivate']);
    Route::post('users/bulk/delete', [UserController::class, 'bulkDelete']);
    Route::apiResource('users', UserController::class);


    #Hacienda Catalogs (Países, Departamentos, Municipios, Distritos)

    // Estadísticas y acciones grupales de countries - DEBEN IR ANTES del apiResource
    Route::get('countries/stats/all', [CountryController::class, 'stats']);
    Route::post('countries/bulk/get', [CountryController::class, 'bulkGet']);
    Route::post('countries/bulk/activate', [CountryController::class, 'bulkActivate']);
    Route::post('countries/bulk/deactivate', [CountryController::class, 'bulkDeactivate']);
    Route::post('countries/bulk/delete', [CountryController::class, 'bulkDelete']);

    Route::apiResource('countries', CountryController::class);

    // Estadísticas y acciones grupales de operation-conditions - DEBEN IR ANTES del apiResource
    Route::get('operation-conditions/stats/all', [OperationConditionController::class, 'stats']);
    Route::post('operation-conditions/bulk/get', [OperationConditionController::class, 'bulkGet']);
    Route::post('operation-conditions/bulk/activate', [OperationConditionController::class, 'bulkActivate']);
    Route::post('operation-conditions/bulk/deactivate', [OperationConditionController::class, 'bulkDeactivate']);
    Route::post('operation-conditions/bulk/delete', [OperationConditionController::class, 'bulkDelete']);

    Route::apiResource('operation-conditions', OperationConditionController::class);

    // Estadísticas y acciones grupales de payment-methods - DEBEN IR ANTES del apiResource
    Route::get('payment-methods/stats/all', [PaymentMethodController::class, 'stats']);
    Route::post('payment-methods/bulk/get', [PaymentMethodController::class, 'bulkGet']);
    Route::post('payment-methods/bulk/activate', [PaymentMethodController::class, 'bulkActivate']);
    Route::post('payment-methods/bulk/deactivate', [PaymentMethodController::class, 'bulkDeactivate']);
    Route::post('payment-methods/bulk/delete', [PaymentMethodController::class, 'bulkDelete']);

    Route::apiResource('payment-methods', PaymentMethodController::class);

    // Estadísticas y acciones grupales de departments - DEBEN IR ANTES del apiResource
    Route::get('departments/stats/all', [DepartmentController::class, 'stats']);
    Route::post('departments/bulk/get', [DepartmentController::class, 'bulkGet']);
    Route::post('departments/bulk/activate', [DepartmentController::class, 'bulkActivate']);
    Route::post('departments/bulk/deactivate', [DepartmentController::class, 'bulkDeactivate']);
    Route::post('departments/bulk/delete', [DepartmentController::class, 'bulkDelete']);

    Route::apiResource('departments', DepartmentController::class);

    // Estadísticas y acciones grupales de municipalities - DEBEN IR ANTES del apiResource
    Route::get('municipalities/stats/all', [MunicipalityController::class, 'stats']);
    Route::post('municipalities/bulk/get', [MunicipalityController::class, 'bulkGet']);
    Route::post('municipalities/bulk/activate', [MunicipalityController::class, 'bulkActivate']);
    Route::post('municipalities/bulk/deactivate', [MunicipalityController::class, 'bulkDeactivate']);
    Route::post('municipalities/bulk/delete', [MunicipalityController::class, 'bulkDelete']);

    Route::apiResource('municipalities', MunicipalityController::class);
    Route::apiResource('districts', DistrictController::class);


    #Empresa sucursal y almacenes (Actividades Económicas, Empresas, Tipos de Establecimientos, Almacenes)
    // Estadísticas y acciones grupales de economic-activities - DEBEN IR ANTES del apiResource
    Route::get('economic-activities/stats/all', [EconomicActivityController::class, 'stats']);
    Route::post('economic-activities/bulk/get', [EconomicActivityController::class, 'bulkGet']);
    Route::post('economic-activities/bulk/activate', [EconomicActivityController::class, 'bulkActivate']);
    Route::post('economic-activities/bulk/deactivate', [EconomicActivityController::class, 'bulkDeactivate']);
    Route::post('economic-activities/bulk/delete', [EconomicActivityController::class, 'bulkDelete']);

    Route::apiResource('economic-activities', EconomicActivityController::class);
    Route::apiResource('company', CompanyController::class);
    Route::apiResource('establishment-types', StablishmentTypeController::class);

    // Acciones grupales de warehouses - DEBEN IR ANTES del apiResource
    Route::post('warehouses/bulk/get', [WarehouseController::class, 'bulkGet']);
    Route::post('warehouses/bulk/activate', [WarehouseController::class, 'bulkActivate']);
    Route::post('warehouses/bulk/deactivate', [WarehouseController::class, 'bulkDeactivate']);
    Route::post('warehouses/bulk/delete', [WarehouseController::class, 'bulkDelete']);

    Route::apiResource('warehouses', WarehouseController::class);

    #Proveedores
    // Estadísticas y acciones grupales de providers-types - DEBEN IR ANTES del apiResource
    Route::get('providers-types/stats/all', [ProvidersTypeController::class, 'stats']);
    Route::post('providers-types/bulk/get', [ProvidersTypeController::class, 'bulkGet']);
    Route::post('providers-types/bulk/activate', [ProvidersTypeController::class, 'bulkActivate']);
    Route::post('providers-types/bulk/deactivate', [ProvidersTypeController::class, 'bulkDeactivate']);
    Route::post('providers-types/bulk/delete', [ProvidersTypeController::class, 'bulkDelete']);

    Route::apiResource('providers-types', ProvidersTypeController::class);

    // Estadísticas y acciones grupales de providers-documents-types - DEBEN IR ANTES del apiResource
    Route::get('providers-documents-types/stats/all', [DocumentsTypesProviderController::class, 'stats']);
    Route::post('providers-documents-types/bulk/get', [DocumentsTypesProviderController::class, 'bulkGet']);
    Route::post('providers-documents-types/bulk/activate', [DocumentsTypesProviderController::class, 'bulkActivate']);
    Route::post('providers-documents-types/bulk/deactivate', [DocumentsTypesProviderController::class, 'bulkDeactivate']);
    Route::post('providers-documents-types/bulk/delete', [DocumentsTypesProviderController::class, 'bulkDelete']);

    Route::apiResource('providers-documents-types', DocumentsTypesProviderController::class);
    Route::apiResource('providers', ProviderController::class);
    Route::apiResource('provider-address-catalogs', ProviderAddressCatalogController::class);

    #Empleados (Cargos, Empleados)
    Route::post('jobs-titles/bulk/get', [JobsTitleController::class, 'bulkGet']);
    Route::post('jobs-titles/bulk/activate', [JobsTitleController::class, 'bulkActivate']);
    Route::post('jobs-titles/bulk/deactivate', [JobsTitleController::class, 'bulkDeactivate']);
    Route::post('jobs-titles/bulk/delete', [JobsTitleController::class, 'bulkDelete']);
    Route::apiResource('jobs-titles', JobsTitleController::class);
    Route::get('employees/all/list', [EmployeeController::class, 'getAll']);
    Route::apiResource('employees', EmployeeController::class);

    #Productos
    Route::apiResource('category-groups', CategoryGroupController::class);
    Route::get('sub-categories/stats/all', [CategoryController::class, 'stats']);
    Route::post('sub-categories/bulk/get', [CategoryController::class, 'bulkGet']);
    Route::post('sub-categories/bulk/activate', [CategoryController::class, 'bulkActivate']);
    Route::post('sub-categories/bulk/deactivate', [CategoryController::class, 'bulkDeactivate']);
    Route::post('sub-categories/bulk/delete', [CategoryController::class, 'bulkDelete']);
    Route::apiResource('sub-categories', CategoryController::class);
    Route::get('brands/stats/all', [BrandController::class, 'stats']);
    Route::post('brands/bulk/get', [BrandController::class, 'bulkGet']);
    Route::post('brands/bulk/activate', [BrandController::class, 'bulkActivate']);
    Route::post('brands/bulk/deactivate', [BrandController::class, 'bulkDeactivate']);
    Route::post('brands/bulk/delete', [BrandController::class, 'bulkDelete']);
    Route::apiResource('brands', BrandController::class);
    Route::get('unit-measurements/stats/all', [UnitMeasurementController::class, 'stats']);
    Route::post('unit-measurements/bulk/get', [UnitMeasurementController::class, 'bulkGet']);
    Route::post('unit-measurements/bulk/activate', [UnitMeasurementController::class, 'bulkActivate']);
    Route::post('unit-measurements/bulk/deactivate', [UnitMeasurementController::class, 'bulkDeactivate']);
    Route::post('unit-measurements/bulk/delete', [UnitMeasurementController::class, 'bulkDelete']);
    Route::apiResource('unit-measurements', UnitMeasurementController::class);

    #Inventarios
    // Estadísticas y acciones por lotes de productos - DEBEN IR ANTES del apiResource
    Route::get('products/stats/all', [ProductController::class, 'stats']);
    Route::post('products/batch/activate', [ProductController::class, 'batchActivate']);
    Route::post('products/batch/deactivate', [ProductController::class, 'batchDeactivate']);
    Route::post('products/batch/delete', [ProductController::class, 'batchDelete']);

    // Ruta para generar reportes
    Route::get('products/report', [ProductController::class, 'generateReport']);

    // Rutas para gestión de imágenes de productos
    Route::delete('products/{productId}/images/{imageId}', [ProductController::class, 'deleteImage']);
    Route::put('products/{productId}/images/{imageId}/primary', [ProductController::class, 'setPrimaryImage']);

    Route::apiResource('products', ProductController::class);
    Route::post('/v1/products/{id}', [ProductController::class, 'update']);

    Route::get('prices/inventory/{idInventory}', [InventoryController::class, 'getPrices']);
    Route::get('inventories/product/{productId}', [InventoryController::class, 'getByProduct']);
    Route::apiResource('inventories', InventoryController::class);
    Route::apiResource('prices', PriceController::class);

    #vehiculos
    // Estadísticas y acciones grupales de plate-types - DEBEN IR ANTES del apiResource
    Route::get('plate-types/stats/all', [PlateTypeController::class, 'stats']);
    Route::post('plate-types/bulk/get', [PlateTypeController::class, 'bulkGet']);
    Route::post('plate-types/bulk/activate', [PlateTypeController::class, 'bulkActivate']);
    Route::post('plate-types/bulk/deactivate', [PlateTypeController::class, 'bulkDeactivate']);
    Route::post('plate-types/bulk/delete', [PlateTypeController::class, 'bulkDelete']);

    Route::apiResource('plate-types', PlateTypeController::class);

    // Estadísticas y acciones grupales de vehicle-models - DEBEN IR ANTES del apiResource
    Route::get('vehicle-models/stats/all', [VehicleModelController::class, 'stats']);
    Route::post('vehicle-models/bulk/get', [VehicleModelController::class, 'bulkGet']);
    Route::post('vehicle-models/bulk/activate', [VehicleModelController::class, 'bulkActivate']);
    Route::post('vehicle-models/bulk/deactivate', [VehicleModelController::class, 'bulkDeactivate']);
    Route::post('vehicle-models/bulk/delete', [VehicleModelController::class, 'bulkDelete']);

    Route::apiResource('vehicle-models', VehicleModelController::class);

    // Estadísticas y acciones grupales de fuel-types - DEBEN IR ANTES del apiResource
    Route::get('fuel-types/stats/all', [FuelTypeController::class, 'stats']);
    Route::post('fuel-types/bulk/get', [FuelTypeController::class, 'bulkGet']);
    Route::post('fuel-types/bulk/activate', [FuelTypeController::class, 'bulkActivate']);
    Route::post('fuel-types/bulk/deactivate', [FuelTypeController::class, 'bulkDeactivate']);
    Route::post('fuel-types/bulk/delete', [FuelTypeController::class, 'bulkDelete']);

    Route::apiResource('fuel-types', FuelTypeController::class);
    Route::apiResource('vehicles', VehicleController::class);

    #Aplicaciones y equivalencias
    Route::apiResource('applications', ApplicationController::class);
    Route::get('applications/product/{id_product}', [ApplicationController::class, 'getApplicationByProduct']);
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
    // Estadísticas y acciones grupales de customer-documents-types - DEBEN IR ANTES del apiResource
    Route::get('customer-documents-types/stats/all', [CustomerDocumentsTypeController::class, 'stats']);
    Route::post('customer-documents-types/bulk/get', [CustomerDocumentsTypeController::class, 'bulkGet']);
    Route::post('customer-documents-types/bulk/activate', [CustomerDocumentsTypeController::class, 'bulkActivate']);
    Route::post('customer-documents-types/bulk/deactivate', [CustomerDocumentsTypeController::class, 'bulkDeactivate']);
    Route::post('customer-documents-types/bulk/delete', [CustomerDocumentsTypeController::class, 'bulkDelete']);

    Route::apiResource('customer-documents-types', CustomerDocumentsTypeController::class);

    // Acciones grupales de customer-types - DEBEN IR ANTES del apiResource
    Route::get('customer-types/stats/all', [CustomerTypeController::class, 'stats']);
    Route::post('customer-types/bulk/get', [CustomerTypeController::class, 'bulkGet']);
    Route::post('customer-types/bulk/activate', [CustomerTypeController::class, 'bulkActivate']);
    Route::post('customer-types/bulk/deactivate', [CustomerTypeController::class, 'bulkDeactivate']);
    Route::post('customer-types/bulk/delete', [CustomerTypeController::class, 'bulkDelete']);

    Route::apiResource('customer-types', CustomerTypeController::class);

    // Customers - rutas especiales antes del resource
    Route::get('customers/stats/all', [CustomerController::class, 'stats']);
    Route::post('customers/bulk/get', [CustomerController::class, 'bulkGet']);
    Route::post('customers/bulk/activate', [CustomerController::class, 'bulkActivate']);
    Route::post('customers/bulk/deactivate', [CustomerController::class, 'bulkDeactivate']);
    Route::post('customers/bulk/delete', [CustomerController::class, 'bulkDelete']);
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
    // Estadísticas y acciones grupales de document-tax - DEBEN IR ANTES del apiResource
    Route::get('document-tax/stats/all', [DocumentTypeController::class, 'stats']);
    Route::post('document-tax/bulk/get', [DocumentTypeController::class, 'bulkGet']);
    Route::post('document-tax/bulk/activate', [DocumentTypeController::class, 'bulkActivate']);
    Route::post('document-tax/bulk/deactivate', [DocumentTypeController::class, 'bulkDeactivate']);
    Route::post('document-tax/bulk/delete', [DocumentTypeController::class, 'bulkDelete']);

    Route::apiResource('document-tax', DocumentTypeController::class);


});






















