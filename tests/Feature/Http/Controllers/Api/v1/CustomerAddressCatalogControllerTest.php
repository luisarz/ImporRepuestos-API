<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\CustomerAddressCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CustomerAddressCatalogController
 */
final class CustomerAddressCatalogControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $customerAddressCatalogs = CustomerAddressCatalog::factory()->count(3)->create();

        $response = $this->get(route('customer-address-catalogs.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerAddressCatalogController::class,
            'store',
            \App\Http\Requests\Api\v1\CustomerAddressCatalogStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $district_id = fake()->numberBetween(-100000, 100000);
        $address_reference = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->post(route('customer-address-catalogs.store'), [
            'district_id' => $district_id,
            'address_reference' => $address_reference,
            'is_active' => $is_active,
        ]);

        $customerAddressCatalogs = CustomerAddressCatalog::query()
            ->where('district_id', $district_id)
            ->where('address_reference', $address_reference)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $customerAddressCatalogs);
        $customerAddressCatalog = $customerAddressCatalogs->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $customerAddressCatalog = CustomerAddressCatalog::factory()->create();

        $response = $this->get(route('customer-address-catalogs.show', $customerAddressCatalog));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerAddressCatalogController::class,
            'update',
            \App\Http\Requests\Api\v1\CustomerAddressCatalogUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $customerAddressCatalog = CustomerAddressCatalog::factory()->create();
        $district_id = fake()->numberBetween(-100000, 100000);
        $address_reference = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->put(route('customer-address-catalogs.update', $customerAddressCatalog), [
            'district_id' => $district_id,
            'address_reference' => $address_reference,
            'is_active' => $is_active,
        ]);

        $customerAddressCatalog->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($district_id, $customerAddressCatalog->district_id);
        $this->assertEquals($address_reference, $customerAddressCatalog->address_reference);
        $this->assertEquals($is_active, $customerAddressCatalog->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $customerAddressCatalog = CustomerAddressCatalog::factory()->create();

        $response = $this->delete(route('customer-address-catalogs.destroy', $customerAddressCatalog));

        $response->assertNoContent();

        $this->assertModelMissing($customerAddressCatalog);
    }
}
