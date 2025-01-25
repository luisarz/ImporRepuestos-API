<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Municipality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\MunicipalityController
 */
final class MunicipalityControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $municipalities = Municipality::factory()->count(3)->create();

        $response = $this->get(route('municipalities.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\MunicipalityController::class,
            'store',
            \App\Http\Requests\Api\v1\MunicipalityStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $department_id = fake()->numberBetween(-100000, 100000);
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('municipalities.store'), [
            'department_id' => $department_id,
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $municipalities = Municipality::query()
            ->where('department_id', $department_id)
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $municipalities);
        $municipality = $municipalities->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $municipality = Municipality::factory()->create();

        $response = $this->get(route('municipalities.show', $municipality));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\MunicipalityController::class,
            'update',
            \App\Http\Requests\Api\v1\MunicipalityUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $municipality = Municipality::factory()->create();
        $department_id = fake()->numberBetween(-100000, 100000);
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('municipalities.update', $municipality), [
            'department_id' => $department_id,
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $municipality->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($department_id, $municipality->department_id);
        $this->assertEquals($code, $municipality->code);
        $this->assertEquals($description, $municipality->description);
        $this->assertEquals($is_active, $municipality->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $municipality = Municipality::factory()->create();

        $response = $this->delete(route('municipalities.destroy', $municipality));

        $response->assertNoContent();

        $this->assertModelMissing($municipality);
    }
}
