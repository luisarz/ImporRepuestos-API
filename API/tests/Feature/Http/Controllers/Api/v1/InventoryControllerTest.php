<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\InventoryController
 */
final class InventoryControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $inventories = Inventory::factory()->count(3)->create();

        $response = $this->get(route('inventories.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\InventoryController::class,
            'store',
            \App\Http\Requests\Api\v1\InventoryStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $warehouse_id = fake()->numberBetween(-100000, 100000);
        $product_id = fake()->numberBetween(-100000, 100000);
        $last_cost_without_tax = fake()->randomFloat(/** decimal_attributes **/);
        $last_cost_with_tax = fake()->randomFloat(/** decimal_attributes **/);
        $stock_actual_quantity = fake()->randomFloat(/** decimal_attributes **/);
        $stock_min = fake()->randomFloat(/** decimal_attributes **/);
        $alert_stock_min = fake()->boolean();
        $stock_max = fake()->randomFloat(/** decimal_attributes **/);
        $alert_stock_max = fake()->boolean();
        $last_purchase = Carbon::parse(fake()->dateTime());
        $is_service = fake()->boolean();

        $response = $this->post(route('inventories.store'), [
            'warehouse_id' => $warehouse_id,
            'product_id' => $product_id,
            'last_cost_without_tax' => $last_cost_without_tax,
            'last_cost_with_tax' => $last_cost_with_tax,
            'stock_actual_quantity' => $stock_actual_quantity,
            'stock_min' => $stock_min,
            'alert_stock_min' => $alert_stock_min,
            'stock_max' => $stock_max,
            'alert_stock_max' => $alert_stock_max,
            'last_purchase' => $last_purchase->toDateTimeString(),
            'is_service' => $is_service,
        ]);

        $inventories = Inventory::query()
            ->where('warehouse_id', $warehouse_id)
            ->where('product_id', $product_id)
            ->where('last_cost_without_tax', $last_cost_without_tax)
            ->where('last_cost_with_tax', $last_cost_with_tax)
            ->where('stock_actual_quantity', $stock_actual_quantity)
            ->where('stock_min', $stock_min)
            ->where('alert_stock_min', $alert_stock_min)
            ->where('stock_max', $stock_max)
            ->where('alert_stock_max', $alert_stock_max)
            ->where('last_purchase', $last_purchase)
            ->where('is_service', $is_service)
            ->get();
        $this->assertCount(1, $inventories);
        $inventory = $inventories->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $inventory = Inventory::factory()->create();

        $response = $this->get(route('inventories.show', $inventory));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\InventoryController::class,
            'update',
            \App\Http\Requests\Api\v1\InventoryUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $inventory = Inventory::factory()->create();
        $warehouse_id = fake()->numberBetween(-100000, 100000);
        $product_id = fake()->numberBetween(-100000, 100000);
        $last_cost_without_tax = fake()->randomFloat(/** decimal_attributes **/);
        $last_cost_with_tax = fake()->randomFloat(/** decimal_attributes **/);
        $stock_actual_quantity = fake()->randomFloat(/** decimal_attributes **/);
        $stock_min = fake()->randomFloat(/** decimal_attributes **/);
        $alert_stock_min = fake()->boolean();
        $stock_max = fake()->randomFloat(/** decimal_attributes **/);
        $alert_stock_max = fake()->boolean();
        $last_purchase = Carbon::parse(fake()->dateTime());
        $is_service = fake()->boolean();

        $response = $this->put(route('inventories.update', $inventory), [
            'warehouse_id' => $warehouse_id,
            'product_id' => $product_id,
            'last_cost_without_tax' => $last_cost_without_tax,
            'last_cost_with_tax' => $last_cost_with_tax,
            'stock_actual_quantity' => $stock_actual_quantity,
            'stock_min' => $stock_min,
            'alert_stock_min' => $alert_stock_min,
            'stock_max' => $stock_max,
            'alert_stock_max' => $alert_stock_max,
            'last_purchase' => $last_purchase->toDateTimeString(),
            'is_service' => $is_service,
        ]);

        $inventory->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($warehouse_id, $inventory->warehouse_id);
        $this->assertEquals($product_id, $inventory->product_id);
        $this->assertEquals($last_cost_without_tax, $inventory->last_cost_without_tax);
        $this->assertEquals($last_cost_with_tax, $inventory->last_cost_with_tax);
        $this->assertEquals($stock_actual_quantity, $inventory->stock_actual_quantity);
        $this->assertEquals($stock_min, $inventory->stock_min);
        $this->assertEquals($alert_stock_min, $inventory->alert_stock_min);
        $this->assertEquals($stock_max, $inventory->stock_max);
        $this->assertEquals($alert_stock_max, $inventory->alert_stock_max);
        $this->assertEquals($last_purchase, $inventory->last_purchase);
        $this->assertEquals($is_service, $inventory->is_service);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $inventory = Inventory::factory()->create();

        $response = $this->delete(route('inventories.destroy', $inventory));

        $response->assertNoContent();

        $this->assertModelMissing($inventory);
    }
}
