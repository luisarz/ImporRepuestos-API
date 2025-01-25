<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\ApplicationController
 */
final class ApplicationControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $applications = Application::factory()->count(3)->create();

        $response = $this->get(route('applications.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ApplicationController::class,
            'store',
            \App\Http\Requests\Api\v1\ApplicationStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $product_id = fake()->numberBetween(-100000, 100000);
        $vehicle_id = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->numberBetween(-100000, 100000);

        $response = $this->post(route('applications.store'), [
            'product_id' => $product_id,
            'vehicle_id' => $vehicle_id,
            'is_active' => $is_active,
        ]);

        $applications = Application::query()
            ->where('product_id', $product_id)
            ->where('vehicle_id', $vehicle_id)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $applications);
        $application = $applications->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $application = Application::factory()->create();

        $response = $this->get(route('applications.show', $application));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ApplicationController::class,
            'update',
            \App\Http\Requests\Api\v1\ApplicationUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $application = Application::factory()->create();
        $product_id = fake()->numberBetween(-100000, 100000);
        $vehicle_id = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->numberBetween(-100000, 100000);

        $response = $this->put(route('applications.update', $application), [
            'product_id' => $product_id,
            'vehicle_id' => $vehicle_id,
            'is_active' => $is_active,
        ]);

        $application->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($product_id, $application->product_id);
        $this->assertEquals($vehicle_id, $application->vehicle_id);
        $this->assertEquals($is_active, $application->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $application = Application::factory()->create();

        $response = $this->delete(route('applications.destroy', $application));

        $response->assertNoContent();

        $this->assertModelMissing($application);
    }
}
