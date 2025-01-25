<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\QuotePurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\QuotePurchaseController
 */
final class QuotePurchaseControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $quotePurchases = QuotePurchase::factory()->count(3)->create();

        $response = $this->get(route('quote-purchases.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\QuotePurchaseController::class,
            'store',
            \App\Http\Requests\Api\v1\QuotePurchaseStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $payment_method = fake()->numberBetween(-100000, 100000);
        $provider = fake()->numberBetween(-100000, 100000);
        $date = Carbon::parse(fake()->date());
        $amount_purchase = fake()->randomFloat(/** decimal_attributes **/);
        $is_active = fake()->boolean();
        $is_purchased = fake()->boolean();
        $is_compared = fake()->boolean();
        $buyer_id = fake()->numberBetween(-100000, 100000);

        $response = $this->post(route('quote-purchases.store'), [
            'payment_method' => $payment_method,
            'provider' => $provider,
            'date' => $date->toDateString(),
            'amount_purchase' => $amount_purchase,
            'is_active' => $is_active,
            'is_purchased' => $is_purchased,
            'is_compared' => $is_compared,
            'buyer_id' => $buyer_id,
        ]);

        $quotePurchases = QuotePurchase::query()
            ->where('payment_method', $payment_method)
            ->where('provider', $provider)
            ->where('date', $date)
            ->where('amount_purchase', $amount_purchase)
            ->where('is_active', $is_active)
            ->where('is_purchased', $is_purchased)
            ->where('is_compared', $is_compared)
            ->where('buyer_id', $buyer_id)
            ->get();
        $this->assertCount(1, $quotePurchases);
        $quotePurchase = $quotePurchases->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $quotePurchase = QuotePurchase::factory()->create();

        $response = $this->get(route('quote-purchases.show', $quotePurchase));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\QuotePurchaseController::class,
            'update',
            \App\Http\Requests\Api\v1\QuotePurchaseUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $quotePurchase = QuotePurchase::factory()->create();
        $payment_method = fake()->numberBetween(-100000, 100000);
        $provider = fake()->numberBetween(-100000, 100000);
        $date = Carbon::parse(fake()->date());
        $amount_purchase = fake()->randomFloat(/** decimal_attributes **/);
        $is_active = fake()->boolean();
        $is_purchased = fake()->boolean();
        $is_compared = fake()->boolean();
        $buyer_id = fake()->numberBetween(-100000, 100000);

        $response = $this->put(route('quote-purchases.update', $quotePurchase), [
            'payment_method' => $payment_method,
            'provider' => $provider,
            'date' => $date->toDateString(),
            'amount_purchase' => $amount_purchase,
            'is_active' => $is_active,
            'is_purchased' => $is_purchased,
            'is_compared' => $is_compared,
            'buyer_id' => $buyer_id,
        ]);

        $quotePurchase->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($payment_method, $quotePurchase->payment_method);
        $this->assertEquals($provider, $quotePurchase->provider);
        $this->assertEquals($date, $quotePurchase->date);
        $this->assertEquals($amount_purchase, $quotePurchase->amount_purchase);
        $this->assertEquals($is_active, $quotePurchase->is_active);
        $this->assertEquals($is_purchased, $quotePurchase->is_purchased);
        $this->assertEquals($is_compared, $quotePurchase->is_compared);
        $this->assertEquals($buyer_id, $quotePurchase->buyer_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $quotePurchase = QuotePurchase::factory()->create();

        $response = $this->delete(route('quote-purchases.destroy', $quotePurchase));

        $response->assertNoContent();

        $this->assertModelMissing($quotePurchase);
    }
}
