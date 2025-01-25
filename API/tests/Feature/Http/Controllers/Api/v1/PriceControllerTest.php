<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Price;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\PriceController
 */
final class PriceControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $prices = Price::factory()->count(3)->create();

        $response = $this->get(route('prices.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PriceController::class,
            'store',
            \App\Http\Requests\Api\v1\PriceStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $price = fake()->randomFloat(/** decimal_attributes **/);

        $response = $this->post(route('prices.store'), [
            'price' => $price,
        ]);

        $prices = Price::query()
            ->where('price', $price)
            ->get();
        $this->assertCount(1, $prices);
        $price = $prices->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $price = Price::factory()->create();

        $response = $this->get(route('prices.show', $price));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PriceController::class,
            'update',
            \App\Http\Requests\Api\v1\PriceUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $price = Price::factory()->create();
        $price = fake()->randomFloat(/** decimal_attributes **/);

        $response = $this->put(route('prices.update', $price), [
            'price' => $price,
        ]);

        $price->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($price, $price->price);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $price = Price::factory()->create();

        $response = $this->delete(route('prices.destroy', $price));

        $response->assertNoContent();

        $this->assertModelMissing($price);
    }
}
