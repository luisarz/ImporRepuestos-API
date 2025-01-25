<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\CustomerAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CustomerAddressController
 */
final class CustomerAddressControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $customerAddresses = CustomerAddress::factory()->count(3)->create();

        $response = $this->get(route('customer-addresses.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerAddressController::class,
            'store',
            \App\Http\Requests\Api\v1\CustomerAddressStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $customer_id = fake()->numberBetween(-100000, 100000);
        $customer_address_id = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->boolean();

        $response = $this->post(route('customer-addresses.store'), [
            'customer_id' => $customer_id,
            'customer_address_id' => $customer_address_id,
            'is_active' => $is_active,
        ]);

        $customerAddresses = CustomerAddress::query()
            ->where('customer_id', $customer_id)
            ->where('customer_address_id', $customer_address_id)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $customerAddresses);
        $customerAddress = $customerAddresses->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $customerAddress = CustomerAddress::factory()->create();

        $response = $this->get(route('customer-addresses.show', $customerAddress));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerAddressController::class,
            'update',
            \App\Http\Requests\Api\v1\CustomerAddressUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $customerAddress = CustomerAddress::factory()->create();
        $customer_id = fake()->numberBetween(-100000, 100000);
        $customer_address_id = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->boolean();

        $response = $this->put(route('customer-addresses.update', $customerAddress), [
            'customer_id' => $customer_id,
            'customer_address_id' => $customer_address_id,
            'is_active' => $is_active,
        ]);

        $customerAddress->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($customer_id, $customerAddress->customer_id);
        $this->assertEquals($customer_address_id, $customerAddress->customer_address_id);
        $this->assertEquals($is_active, $customerAddress->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $customerAddress = CustomerAddress::factory()->create();

        $response = $this->delete(route('customer-addresses.destroy', $customerAddress));

        $response->assertNoContent();

        $this->assertModelMissing($customerAddress);
    }
}
