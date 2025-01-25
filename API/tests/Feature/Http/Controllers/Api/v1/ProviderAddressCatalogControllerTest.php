<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\ProviderAddressCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\ProviderAddressCatalogController
 */
final class ProviderAddressCatalogControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $providerAddressCatalogs = ProviderAddressCatalog::factory()->count(3)->create();

        $response = $this->get(route('provider-address-catalogs.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProviderAddressCatalogController::class,
            'store',
            \App\Http\Requests\Api\v1\ProviderAddressCatalogStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $district_id = fake()->numberBetween(-100000, 100000);
        $address_reference = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->post(route('provider-address-catalogs.store'), [
            'district_id' => $district_id,
            'address_reference' => $address_reference,
            'is_active' => $is_active,
        ]);

        $providerAddressCatalogs = ProviderAddressCatalog::query()
            ->where('district_id', $district_id)
            ->where('address_reference', $address_reference)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $providerAddressCatalogs);
        $providerAddressCatalog = $providerAddressCatalogs->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $providerAddressCatalog = ProviderAddressCatalog::factory()->create();

        $response = $this->get(route('provider-address-catalogs.show', $providerAddressCatalog));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProviderAddressCatalogController::class,
            'update',
            \App\Http\Requests\Api\v1\ProviderAddressCatalogUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $providerAddressCatalog = ProviderAddressCatalog::factory()->create();
        $district_id = fake()->numberBetween(-100000, 100000);
        $address_reference = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->put(route('provider-address-catalogs.update', $providerAddressCatalog), [
            'district_id' => $district_id,
            'address_reference' => $address_reference,
            'is_active' => $is_active,
        ]);

        $providerAddressCatalog->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($district_id, $providerAddressCatalog->district_id);
        $this->assertEquals($address_reference, $providerAddressCatalog->address_reference);
        $this->assertEquals($is_active, $providerAddressCatalog->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $providerAddressCatalog = ProviderAddressCatalog::factory()->create();

        $response = $this->delete(route('provider-address-catalogs.destroy', $providerAddressCatalog));

        $response->assertNoContent();

        $this->assertModelMissing($providerAddressCatalog);
    }
}
