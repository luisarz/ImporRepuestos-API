<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\SaleItemController
 */
final class SaleItemControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $saleItems = SaleItem::factory()->count(3)->create();

        $response = $this->get(route('sale-items.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\SaleItemController::class,
            'store',
            \App\Http\Requests\Api\v1\SaleItemStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $sale_id = fake()->numberBetween(-100000, 100000);
        $inventory_id = fake()->numberBetween(-100000, 100000);
        $batch_id = fake()->numberBetween(-100000, 100000);
        $saled = fake()->boolean();
        $quantity = fake()->randomFloat(/** decimal_attributes **/);
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $discount = fake()->randomFloat(/** decimal_attributes **/);
        $total = fake()->randomFloat(/** decimal_attributes **/);
        $is_saled = fake()->boolean();
        $is_active = fake()->boolean();

        $response = $this->post(route('sale-items.store'), [
            'sale_id' => $sale_id,
            'inventory_id' => $inventory_id,
            'batch_id' => $batch_id,
            'saled' => $saled,
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount,
            'total' => $total,
            'is_saled' => $is_saled,
            'is_active' => $is_active,
        ]);

        $saleItems = SaleItem::query()
            ->where('sale_id', $sale_id)
            ->where('inventory_id', $inventory_id)
            ->where('batch_id', $batch_id)
            ->where('saled', $saled)
            ->where('quantity', $quantity)
            ->where('price', $price)
            ->where('discount', $discount)
            ->where('total', $total)
            ->where('is_saled', $is_saled)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $saleItems);
        $saleItem = $saleItems->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $saleItem = SaleItem::factory()->create();

        $response = $this->get(route('sale-items.show', $saleItem));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\SaleItemController::class,
            'update',
            \App\Http\Requests\Api\v1\SaleItemUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $saleItem = SaleItem::factory()->create();
        $sale_id = fake()->numberBetween(-100000, 100000);
        $inventory_id = fake()->numberBetween(-100000, 100000);
        $batch_id = fake()->numberBetween(-100000, 100000);
        $saled = fake()->boolean();
        $quantity = fake()->randomFloat(/** decimal_attributes **/);
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $discount = fake()->randomFloat(/** decimal_attributes **/);
        $total = fake()->randomFloat(/** decimal_attributes **/);
        $is_saled = fake()->boolean();
        $is_active = fake()->boolean();

        $response = $this->put(route('sale-items.update', $saleItem), [
            'sale_id' => $sale_id,
            'inventory_id' => $inventory_id,
            'batch_id' => $batch_id,
            'saled' => $saled,
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount,
            'total' => $total,
            'is_saled' => $is_saled,
            'is_active' => $is_active,
        ]);

        $saleItem->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($sale_id, $saleItem->sale_id);
        $this->assertEquals($inventory_id, $saleItem->inventory_id);
        $this->assertEquals($batch_id, $saleItem->batch_id);
        $this->assertEquals($saled, $saleItem->saled);
        $this->assertEquals($quantity, $saleItem->quantity);
        $this->assertEquals($price, $saleItem->price);
        $this->assertEquals($discount, $saleItem->discount);
        $this->assertEquals($total, $saleItem->total);
        $this->assertEquals($is_saled, $saleItem->is_saled);
        $this->assertEquals($is_active, $saleItem->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $saleItem = SaleItem::factory()->create();

        $response = $this->delete(route('sale-items.destroy', $saleItem));

        $response->assertNoContent();

        $this->assertModelMissing($saleItem);
    }
}
