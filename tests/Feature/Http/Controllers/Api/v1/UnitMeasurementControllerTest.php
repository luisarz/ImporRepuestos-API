<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\UnitMeasurement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\UnitMeasurementController
 */
final class UnitMeasurementControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $unitMeasurements = UnitMeasurement::factory()->count(3)->create();

        $response = $this->get(route('unit-measurements.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\UnitMeasurementController::class,
            'store',
            \App\Http\Requests\Api\v1\UnitMeasurementStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('unit-measurements.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $unitMeasurements = UnitMeasurement::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $unitMeasurements);
        $unitMeasurement = $unitMeasurements->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $unitMeasurement = UnitMeasurement::factory()->create();

        $response = $this->get(route('unit-measurements.show', $unitMeasurement));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\UnitMeasurementController::class,
            'update',
            \App\Http\Requests\Api\v1\UnitMeasurementUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $unitMeasurement = UnitMeasurement::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('unit-measurements.update', $unitMeasurement), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $unitMeasurement->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $unitMeasurement->code);
        $this->assertEquals($description, $unitMeasurement->description);
        $this->assertEquals($is_active, $unitMeasurement->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $unitMeasurement = UnitMeasurement::factory()->create();

        $response = $this->delete(route('unit-measurements.destroy', $unitMeasurement));

        $response->assertNoContent();

        $this->assertModelMissing($unitMeasurement);
    }
}
