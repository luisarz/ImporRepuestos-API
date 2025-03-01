<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\QuotePurchaseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\QuotePurchaseItemController
 */
final class QuotePurchaseItemControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $quotePurchaseItems = QuotePurchaseItem::factory()->count(3)->create();

        $response = $this->get(route('quote-purchase-items.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\QuotePurchaseItemController::class,
            'store',
            \App\Http\Requests\Api\v1\QuotePurchaseItemStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $quote_purchase_id = fake()->numberBetween(-100000, 100000);
        $inventory_id = fake()->numberBetween(-100000, 100000);
        $quantity = fake()->randomFloat(/** decimal_attributes **/);
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $discount = fake()->randomFloat(/** decimal_attributes **/);
        $total = fake()->randomFloat(/** decimal_attributes **/);
        $is_compared = fake()->numberBetween(-100000, 100000);
        $is_purchased = fake()->boolean();

        $response = $this->post(route('quote-purchase-items.store'), [
            'quote_purchase_id' => $quote_purchase_id,
            'inventory_id' => $inventory_id,
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount,
            'total' => $total,
            'is_compared' => $is_compared,
            'is_purchased' => $is_purchased,
        ]);

        $quotePurchaseItems = QuotePurchaseItem::query()
            ->where('quote_purchase_id', $quote_purchase_id)
            ->where('inventory_id', $inventory_id)
            ->where('quantity', $quantity)
            ->where('price', $price)
            ->where('discount', $discount)
            ->where('total', $total)
            ->where('is_compared', $is_compared)
            ->where('is_purchased', $is_purchased)
            ->get();
        $this->assertCount(1, $quotePurchaseItems);
        $quotePurchaseItem = $quotePurchaseItems->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $quotePurchaseItem = QuotePurchaseItem::factory()->create();

        $response = $this->get(route('quote-purchase-items.show', $quotePurchaseItem));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\QuotePurchaseItemController::class,
            'update',
            \App\Http\Requests\Api\v1\QuotePurchaseItemUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $quotePurchaseItem = QuotePurchaseItem::factory()->create();
        $quote_purchase_id = fake()->numberBetween(-100000, 100000);
        $inventory_id = fake()->numberBetween(-100000, 100000);
        $quantity = fake()->randomFloat(/** decimal_attributes **/);
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $discount = fake()->randomFloat(/** decimal_attributes **/);
        $total = fake()->randomFloat(/** decimal_attributes **/);
        $is_compared = fake()->numberBetween(-100000, 100000);
        $is_purchased = fake()->boolean();

        $response = $this->put(route('quote-purchase-items.update', $quotePurchaseItem), [
            'quote_purchase_id' => $quote_purchase_id,
            'inventory_id' => $inventory_id,
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount,
            'total' => $total,
            'is_compared' => $is_compared,
            'is_purchased' => $is_purchased,
        ]);

        $quotePurchaseItem->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($quote_purchase_id, $quotePurchaseItem->quote_purchase_id);
        $this->assertEquals($inventory_id, $quotePurchaseItem->inventory_id);
        $this->assertEquals($quantity, $quotePurchaseItem->quantity);
        $this->assertEquals($price, $quotePurchaseItem->price);
        $this->assertEquals($discount, $quotePurchaseItem->discount);
        $this->assertEquals($total, $quotePurchaseItem->total);
        $this->assertEquals($is_compared, $quotePurchaseItem->is_compared);
        $this->assertEquals($is_purchased, $quotePurchaseItem->is_purchased);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $quotePurchaseItem = QuotePurchaseItem::factory()->create();

        $response = $this->delete(route('quote-purchase-items.destroy', $quotePurchaseItem));

        $response->assertNoContent();

        $this->assertModelMissing($quotePurchaseItem);
    }
}
