<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\DistrictController
 */
final class DistrictControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $districts = District::factory()->count(3)->create();

        $response = $this->get(route('districts.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\DistrictController::class,
            'store',
            \App\Http\Requests\Api\v1\DistrictStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $municipality_id = fake()->numberBetween(-100000, 100000);
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('districts.store'), [
            'municipality_id' => $municipality_id,
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $districts = District::query()
            ->where('municipality_id', $municipality_id)
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $districts);
        $district = $districts->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $district = District::factory()->create();

        $response = $this->get(route('districts.show', $district));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\DistrictController::class,
            'update',
            \App\Http\Requests\Api\v1\DistrictUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $district = District::factory()->create();
        $municipality_id = fake()->numberBetween(-100000, 100000);
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('districts.update', $district), [
            'municipality_id' => $municipality_id,
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $district->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($municipality_id, $district->municipality_id);
        $this->assertEquals($code, $district->code);
        $this->assertEquals($description, $district->description);
        $this->assertEquals($is_active, $district->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $district = District::factory()->create();

        $response = $this->delete(route('districts.destroy', $district));

        $response->assertNoContent();

        $this->assertModelMissing($district);
    }
}
