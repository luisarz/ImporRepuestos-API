<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\WarehouseController
 */
final class WarehouseControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $warehouses = Warehouse::factory()->count(3)->create();

        $response = $this->get(route('warehouses.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\WarehouseController::class,
            'store',
            \App\Http\Requests\Api\v1\WarehouseStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $company_id = fake()->numberBetween(-100000, 100000);
        $stablishment_type = fake()->numberBetween(-100000, 100000);
        $name = fake()->name();
        $nrc = fake()->word();
        $nit = fake()->word();
        $district_id = fake()->numberBetween(-100000, 100000);
        $economic_activity_id = fake()->numberBetween(-100000, 100000);
        $address = fake()->word();
        $phone = fake()->phoneNumber();
        $email = fake()->safeEmail();
        $product_prices = fake()->numberBetween(-10000, 10000);

        $response = $this->post(route('warehouses.store'), [
            'company_id' => $company_id,
            'stablishment_type' => $stablishment_type,
            'name' => $name,
            'nrc' => $nrc,
            'nit' => $nit,
            'district_id' => $district_id,
            'economic_activity_id' => $economic_activity_id,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'product_prices' => $product_prices,
        ]);

        $warehouses = Warehouse::query()
            ->where('company_id', $company_id)
            ->where('stablishment_type', $stablishment_type)
            ->where('name', $name)
            ->where('nrc', $nrc)
            ->where('nit', $nit)
            ->where('district_id', $district_id)
            ->where('economic_activity_id', $economic_activity_id)
            ->where('address', $address)
            ->where('phone', $phone)
            ->where('email', $email)
            ->where('product_prices', $product_prices)
            ->get();
        $this->assertCount(1, $warehouses);
        $warehouse = $warehouses->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->get(route('warehouses.show', $warehouse));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\WarehouseController::class,
            'update',
            \App\Http\Requests\Api\v1\WarehouseUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $warehouse = Warehouse::factory()->create();
        $company_id = fake()->numberBetween(-100000, 100000);
        $stablishment_type = fake()->numberBetween(-100000, 100000);
        $name = fake()->name();
        $nrc = fake()->word();
        $nit = fake()->word();
        $district_id = fake()->numberBetween(-100000, 100000);
        $economic_activity_id = fake()->numberBetween(-100000, 100000);
        $address = fake()->word();
        $phone = fake()->phoneNumber();
        $email = fake()->safeEmail();
        $product_prices = fake()->numberBetween(-10000, 10000);

        $response = $this->put(route('warehouses.update', $warehouse), [
            'company_id' => $company_id,
            'stablishment_type' => $stablishment_type,
            'name' => $name,
            'nrc' => $nrc,
            'nit' => $nit,
            'district_id' => $district_id,
            'economic_activity_id' => $economic_activity_id,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'product_prices' => $product_prices,
        ]);

        $warehouse->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($company_id, $warehouse->company_id);
        $this->assertEquals($stablishment_type, $warehouse->stablishment_type);
        $this->assertEquals($name, $warehouse->name);
        $this->assertEquals($nrc, $warehouse->nrc);
        $this->assertEquals($nit, $warehouse->nit);
        $this->assertEquals($district_id, $warehouse->district_id);
        $this->assertEquals($economic_activity_id, $warehouse->economic_activity_id);
        $this->assertEquals($address, $warehouse->address);
        $this->assertEquals($phone, $warehouse->phone);
        $this->assertEquals($email, $warehouse->email);
        $this->assertEquals($product_prices, $warehouse->product_prices);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->delete(route('warehouses.destroy', $warehouse));

        $response->assertNoContent();

        $this->assertModelMissing($warehouse);
    }
}
