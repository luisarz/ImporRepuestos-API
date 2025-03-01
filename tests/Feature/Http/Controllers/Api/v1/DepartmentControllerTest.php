<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\DepartmentController
 */
final class DepartmentControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $departments = Department::factory()->count(3)->create();

        $response = $this->get(route('departments.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\DepartmentController::class,
            'store',
            \App\Http\Requests\Api\v1\DepartmentStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $country_id = fake()->numberBetween(-100000, 100000);
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('departments.store'), [
            'country_id' => $country_id,
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $departments = Department::query()
            ->where('country_id', $country_id)
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $departments);
        $department = $departments->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $department = Department::factory()->create();

        $response = $this->get(route('departments.show', $department));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\DepartmentController::class,
            'update',
            \App\Http\Requests\Api\v1\DepartmentUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $department = Department::factory()->create();
        $country_id = fake()->numberBetween(-100000, 100000);
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('departments.update', $department), [
            'country_id' => $country_id,
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $department->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($country_id, $department->country_id);
        $this->assertEquals($code, $department->code);
        $this->assertEquals($description, $department->description);
        $this->assertEquals($is_active, $department->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $department = Department::factory()->create();

        $response = $this->delete(route('departments.destroy', $department));

        $response->assertNoContent();

        $this->assertModelMissing($department);
    }
}
