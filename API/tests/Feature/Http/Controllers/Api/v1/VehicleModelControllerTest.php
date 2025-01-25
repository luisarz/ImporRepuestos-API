<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\VehicleModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\VehicleModelController
 */
final class VehicleModelControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $vehicleModels = VehicleModel::factory()->count(3)->create();

        $response = $this->get(route('vehicle-models.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\VehicleModelController::class,
            'store',
            \App\Http\Requests\Api\v1\VehicleModelStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('vehicle-models.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $vehicleModels = VehicleModel::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $vehicleModels);
        $vehicleModel = $vehicleModels->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $vehicleModel = VehicleModel::factory()->create();

        $response = $this->get(route('vehicle-models.show', $vehicleModel));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\VehicleModelController::class,
            'update',
            \App\Http\Requests\Api\v1\VehicleModelUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $vehicleModel = VehicleModel::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('vehicle-models.update', $vehicleModel), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $vehicleModel->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $vehicleModel->code);
        $this->assertEquals($description, $vehicleModel->description);
        $this->assertEquals($is_active, $vehicleModel->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $vehicleModel = VehicleModel::factory()->create();

        $response = $this->delete(route('vehicle-models.destroy', $vehicleModel));

        $response->assertNoContent();

        $this->assertModelMissing($vehicleModel);
    }
}
