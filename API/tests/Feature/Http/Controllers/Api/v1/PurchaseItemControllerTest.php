<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\PurchaseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\PurchaseItemController
 */
final class PurchaseItemControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $purchaseItems = PurchaseItem::factory()->count(3)->create();

        $response = $this->get(route('purchase-items.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PurchaseItemController::class,
            'store',
            \App\Http\Requests\Api\v1\PurchaseItemStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $purchase_id = fake()->numberBetween(-100000, 100000);
        $batch_id = fake()->numberBetween(-100000, 100000);
        $is_purched = fake()->boolean();
        $quantity = fake()->randomFloat(/** decimal_attributes **/);
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $discount = fake()->randomFloat(/** decimal_attributes **/);
        $total = fake()->randomFloat(/** decimal_attributes **/);

        $response = $this->post(route('purchase-items.store'), [
            'purchase_id' => $purchase_id,
            'batch_id' => $batch_id,
            'is_purched' => $is_purched,
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount,
            'total' => $total,
        ]);

        $purchaseItems = PurchaseItem::query()
            ->where('purchase_id', $purchase_id)
            ->where('batch_id', $batch_id)
            ->where('is_purched', $is_purched)
            ->where('quantity', $quantity)
            ->where('price', $price)
            ->where('discount', $discount)
            ->where('total', $total)
            ->get();
        $this->assertCount(1, $purchaseItems);
        $purchaseItem = $purchaseItems->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $purchaseItem = PurchaseItem::factory()->create();

        $response = $this->get(route('purchase-items.show', $purchaseItem));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PurchaseItemController::class,
            'update',
            \App\Http\Requests\Api\v1\PurchaseItemUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $purchaseItem = PurchaseItem::factory()->create();
        $purchase_id = fake()->numberBetween(-100000, 100000);
        $batch_id = fake()->numberBetween(-100000, 100000);
        $is_purched = fake()->boolean();
        $quantity = fake()->randomFloat(/** decimal_attributes **/);
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $discount = fake()->randomFloat(/** decimal_attributes **/);
        $total = fake()->randomFloat(/** decimal_attributes **/);

        $response = $this->put(route('purchase-items.update', $purchaseItem), [
            'purchase_id' => $purchase_id,
            'batch_id' => $batch_id,
            'is_purched' => $is_purched,
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount,
            'total' => $total,
        ]);

        $purchaseItem->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($purchase_id, $purchaseItem->purchase_id);
        $this->assertEquals($batch_id, $purchaseItem->batch_id);
        $this->assertEquals($is_purched, $purchaseItem->is_purched);
        $this->assertEquals($quantity, $purchaseItem->quantity);
        $this->assertEquals($price, $purchaseItem->price);
        $this->assertEquals($discount, $purchaseItem->discount);
        $this->assertEquals($total, $purchaseItem->total);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $purchaseItem = PurchaseItem::factory()->create();

        $response = $this->delete(route('purchase-items.destroy', $purchaseItem));

        $response->assertNoContent();

        $this->assertModelMissing($purchaseItem);
    }
}
