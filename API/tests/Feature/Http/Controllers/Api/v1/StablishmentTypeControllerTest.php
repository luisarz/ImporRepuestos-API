<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\StablishmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\StablishmentTypeController
 */
final class StablishmentTypeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $stablishmentTypes = StablishmentType::factory()->count(3)->create();

        $response = $this->get(route('stablishment-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\StablishmentTypeController::class,
            'store',
            \App\Http\Requests\Api\v1\StablishmentTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('stablishment-types.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $stablishmentTypes = StablishmentType::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $stablishmentTypes);
        $stablishmentType = $stablishmentTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $stablishmentType = StablishmentType::factory()->create();

        $response = $this->get(route('stablishment-types.show', $stablishmentType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\StablishmentTypeController::class,
            'update',
            \App\Http\Requests\Api\v1\StablishmentTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $stablishmentType = StablishmentType::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('stablishment-types.update', $stablishmentType), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $stablishmentType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $stablishmentType->code);
        $this->assertEquals($description, $stablishmentType->description);
        $this->assertEquals($is_active, $stablishmentType->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $stablishmentType = StablishmentType::factory()->create();

        $response = $this->delete(route('stablishment-types.destroy', $stablishmentType));

        $response->assertNoContent();

        $this->assertModelMissing($stablishmentType);
    }
}
