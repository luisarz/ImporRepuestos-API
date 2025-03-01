<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\PlateType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\PlateTypeController
 */
final class PlateTypeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $plateTypes = PlateType::factory()->count(3)->create();

        $response = $this->get(route('plate-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PlateTypeController::class,
            'store',
            \App\Http\Requests\Api\v1\PlateTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('plate-types.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $plateTypes = PlateType::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $plateTypes);
        $plateType = $plateTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $plateType = PlateType::factory()->create();

        $response = $this->get(route('plate-types.show', $plateType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PlateTypeController::class,
            'update',
            \App\Http\Requests\Api\v1\PlateTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $plateType = PlateType::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('plate-types.update', $plateType), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $plateType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $plateType->code);
        $this->assertEquals($description, $plateType->description);
        $this->assertEquals($is_active, $plateType->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $plateType = PlateType::factory()->create();

        $response = $this->delete(route('plate-types.destroy', $plateType));

        $response->assertNoContent();

        $this->assertModelMissing($plateType);
    }
}
