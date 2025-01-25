<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\ProviderAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\ProviderAddressController
 */
final class ProviderAddressControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $providerAddresses = ProviderAddress::factory()->count(3)->create();

        $response = $this->get(route('provider-addresses.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProviderAddressController::class,
            'store',
            \App\Http\Requests\Api\v1\ProviderAddressStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $provider_id = fake()->numberBetween(-100000, 100000);
        $address_id = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->boolean();

        $response = $this->post(route('provider-addresses.store'), [
            'provider_id' => $provider_id,
            'address_id' => $address_id,
            'is_active' => $is_active,
        ]);

        $providerAddresses = ProviderAddress::query()
            ->where('provider_id', $provider_id)
            ->where('address_id', $address_id)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $providerAddresses);
        $providerAddress = $providerAddresses->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $providerAddress = ProviderAddress::factory()->create();

        $response = $this->get(route('provider-addresses.show', $providerAddress));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProviderAddressController::class,
            'update',
            \App\Http\Requests\Api\v1\ProviderAddressUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $providerAddress = ProviderAddress::factory()->create();
        $provider_id = fake()->numberBetween(-100000, 100000);
        $address_id = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->boolean();

        $response = $this->put(route('provider-addresses.update', $providerAddress), [
            'provider_id' => $provider_id,
            'address_id' => $address_id,
            'is_active' => $is_active,
        ]);

        $providerAddress->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($provider_id, $providerAddress->provider_id);
        $this->assertEquals($address_id, $providerAddress->address_id);
        $this->assertEquals($is_active, $providerAddress->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $providerAddress = ProviderAddress::factory()->create();

        $response = $this->delete(route('provider-addresses.destroy', $providerAddress));

        $response->assertNoContent();

        $this->assertModelMissing($providerAddress);
    }
}
