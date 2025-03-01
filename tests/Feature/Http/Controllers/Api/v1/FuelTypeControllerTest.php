<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\FuelType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\FuelTypeController
 */
final class FuelTypeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $fuelTypes = FuelType::factory()->count(3)->create();

        $response = $this->get(route('fuel-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\FuelTypeController::class,
            'store',
            \App\Http\Requests\Api\v1\FuelTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('fuel-types.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $fuelTypes = FuelType::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $fuelTypes);
        $fuelType = $fuelTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $fuelType = FuelType::factory()->create();

        $response = $this->get(route('fuel-types.show', $fuelType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\FuelTypeController::class,
            'update',
            \App\Http\Requests\Api\v1\FuelTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $fuelType = FuelType::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('fuel-types.update', $fuelType), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $fuelType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $fuelType->code);
        $this->assertEquals($description, $fuelType->description);
        $this->assertEquals($is_active, $fuelType->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $fuelType = FuelType::factory()->create();

        $response = $this->delete(route('fuel-types.destroy', $fuelType));

        $response->assertNoContent();

        $this->assertModelMissing($fuelType);
    }
}
