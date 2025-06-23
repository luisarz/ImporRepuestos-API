<?php

use App\Models\Batch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\InventoriesBatch;
use App\Models\Inventory;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::get('/migrar', function () {
    \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    CategoryGroup::truncate();
    $grupos = DB::connection('mariadb2')->table('grupos')->get();
    foreach ($grupos as $grupo) {
        $newGroup = new CategoryGroup();
        $newGroup->code = $grupo->codigo;
        $newGroup->name = $grupo->grupo;
        $newGroup->active = true;
        $newGroup->save();
    }
    $newGroupTemp = new CategoryGroup();
    $newGroupTemp->code = "0";
    $newGroupTemp->name = "Grupo Temporal";
    $newGroupTemp->active = true;
    $newGroupTemp->save();

    Category::truncate();
    $categorias = DB::connection('mariadb2')->table('categorias')->get();
    foreach ($categorias as $categoria) {
        $newCategory = new Category();
        $newCategory->code = $categoria->codigo;
        $newCategory->category_parent_id = $categoria->idGrupo;
        $newCategory->description = $categoria->categoria;
        $newCategory->commission_percentage = 0;
        $newCategory->is_active = true;
        $newCategory->save();
    }

    $newCategoryTemp = new Category();
    $newCategoryTemp->code = "0000";
    $newCategoryTemp->category_parent_id = $newGroupTemp->id;
    $newCategoryTemp->description = "Categoria Temporal";
    $newCategoryTemp->commission_percentage = 0;
    $newCategoryTemp->is_active = true;
    $newCategoryTemp->save();


//        dd('Categorías '. $categoriasCreated . ' creadas');
    $ids = [
        41874, 48053, 39227, 39141, 52355, 39228, 39111, 39149, 39145, 21695,
        22524, 39142, 7557, 24882, 20297, 39230, 20502, 17872, 39152, 26731,
        39128, 26911, 39125, 21411, 26605, 46989, 26686, 26695, 11640, 24808,
        39107, 39231, 26692, 6913, 22259, 52337, 21845, 36917, 28527, 44766,
        21844, 77779, 53643, 39109, 44763, 39151, 50160, 26330, 8637, 32113,
        21582, 26562, 41876, 9934, 2121, 39156, 78261, 7471, 27431, 39542,
        28012, 76917, 64199, 13957, 7466, 5738, 79190, 27573, 20922, 26595,
        34758, 26623, 5842, 33600, 77780, 44771, 27738, 79142, 22523, 8570,
        8887, 9327, 76919, 53405, 51085, 1844, 39121, 14080, 41042, 74274,
        22258, 41920, 2886, 39105, 76880, 41796, 48918, 24943, 46990, 44978
    ];
    Product::truncate();
    Inventory::truncate();
    Price::truncate();
    Batch::truncate();
    InventoriesBatch::truncate();


    $products = DB::connection('mariadb2')->table('productos')->whereIn('idProducto', $ids)->get();
    $lineasNuevas = 0;
    $marcasNuevas = 0;
    $productosNuevos = 0;
    $inventarioNuevo = 0;
    $productosNoCrados = [];
    $inventariosNoCreados = [];
    foreach ($products as $producto) {
        try {
            $nuevo = new Product();
            $nuevo->id = $producto->idProducto;
            $nuevo->code = trim($producto->codigo);
            $nuevo->original_code = trim($producto->codoriginal);
            $nuevo->barcode = trim($producto->codbarra);
            $nuevo->description = trim($producto->nombre);
            $nuevo->unit_measurement_id = 1;
            $nuevo->description_measurement_id = $producto->medidas;
            $id_marca = 0;
            $brand = Brand::where('description', '=', trim($producto->marca))->first();
            if (!$brand) {
                $newMarca = new Brand();
                $newMarca->code = '-';
                $newMarca->description = trim($producto->marca);
                $newMarca->is_active = true;
                $newMarca->save();
                $id_marca = $newMarca->id;
            } else {
                $id_marca = $brand->id;
            }
            $nuevo->brand_id = $id_marca;

            $id_category = $newCategoryTemp->id;
            $category = Category::where('id', '=', trim($producto->idCategoria))->first();
            if ($category) {
                $id_category = $category->id;
            }

            $nuevo->category_id = $id_category;
            $nuevo->is_service = false;
            $nuevo->provider_id = 1;
            $nuevo->is_taxed = true;
            $nuevo->is_service = false;
            $nuevo->is_discontinued = false;
            $nuevo->is_not_purchasable = false;
            $nuevo->is_temp = false;
            $nuevo->save();


            //llenar el inventario
            $existenciasTotales = DB::connection('mariadb2')
                ->table('inventario')
                ->where('idProducto', $producto->idProducto)
                ->where('idSucursal', 1)
                ->sum('existencia');

            $pricesOld = DB::connection('mariadb2')->table('precios')
                ->where('idProducto', $producto->idProducto)
                ->where('idSucursal', '=', 1)
                ->first();


            $inventario = new Inventory();
            $inventario->product_id = $producto->idProducto;
            $inventario->warehouse_id = 1;


            $cost = $pricesOld->costo ?? 0; // Si $producto->cost es null, asigna 0
            $inventario->last_cost_without_tax = $cost;
            $inventario->last_cost_with_tax = $cost > 0 ? $cost * 1.13 : 0; // Evita multiplicar si es 0

            $stock = $existenciasTotales ?? 0;
            $inventario->stock_actual_quantity = $stock;
            $inventario->stock_min = 0;
            $inventario->stock_max = 0;
            $inventario->alert_stock_max = true;
            $inventario->last_purchase = now();
            $inventario->is_temp = false;
            $inventario->provider_id = 1;
            $inventario->is_active = true;
            $inventario->save();
            Log::info("Inventario creado para el producto: {$nuevo->description} con ID: {$nuevo->id}");


            $lote = new Batch();
            $lote->code = $inventario->product->code;
            $lote->origen_code = 1;
            $lote->inventory_id = $inventario->id;
            $lote->incoming_date = now();
            $lote->expiration_date = null;
            $lote->initial_quantity = $stock;
            $lote->available_quantity = $stock;
            $lote->observations = "Lote de Inventario inicial";
            $lote->is_active = 1;
            $lote->save();


            //Crear un lote de inventario
            $inventario->inventoryBatches()->create([
                'inventory_id' => $inventario->id,
                'id_batch' => $lote->id,
                'quantity' => $stock ?? 0,
                'operation_date' => now(),
            ]);


//            foreach ($pricesOld as $precio) {
            $precio = new Price();
            $newPrice = $pricesOld->pmayoreo ?? 0;
            $newLabel = "Mayoreo";
            $precio->inventory_id = $inventario->id;
            $precio->price_description = "Mayoreo";
            $precio->price = $newPrice ?? 0;
            $precio->utility = 0;
            $precio->is_default = false;
            $precio->quantity = 1;
            $precio->is_active = true;
            $precio->save();
            $precio = new Price();

            $newLabel = "Detalle";
            $newPrice = $pricesOld->detalle ?? 0;
            $precio->inventory_id = $inventario->id;
            $precio->price_description = $newLabel;
            $precio->price = $newPrice ?? 0;
            $precio->utility = 0;
            $precio->is_default = true;
            $precio->quantity = 1;
            $precio->is_active = true;
            $precio->save();


        } catch (\Exception $e) {
            dd($e);
        }


    }
    \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    dd($productosNoCrados,
        $inventariosNoCreados);
});

require __DIR__ . '/auth.php';





