<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\CategoryGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CategoryGroupController
 */
final class CategoryGroupControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $categoryGroups = CategoryGroup::factory()->count(3)->create();

        $response = $this->get(route('category-groups.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CategoryGroupController::class,
            'store',
            \App\Http\Requests\Api\v1\CategoryGroupStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $name = fake()->name();
        $active = fake()->boolean();

        $response = $this->post(route('category-groups.store'), [
            'code' => $code,
            'name' => $name,
            'active' => $active,
        ]);

        $categoryGroups = CategoryGroup::query()
            ->where('code', $code)
            ->where('name', $name)
            ->where('active', $active)
            ->get();
        $this->assertCount(1, $categoryGroups);
        $categoryGroup = $categoryGroups->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $categoryGroup = CategoryGroup::factory()->create();

        $response = $this->get(route('category-groups.show', $categoryGroup));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CategoryGroupController::class,
            'update',
            \App\Http\Requests\Api\v1\CategoryGroupUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $categoryGroup = CategoryGroup::factory()->create();
        $code = fake()->word();
        $name = fake()->name();
        $active = fake()->boolean();

        $response = $this->put(route('category-groups.update', $categoryGroup), [
            'code' => $code,
            'name' => $name,
            'active' => $active,
        ]);

        $categoryGroup->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $categoryGroup->code);
        $this->assertEquals($name, $categoryGroup->name);
        $this->assertEquals($active, $categoryGroup->active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $categoryGroup = CategoryGroup::factory()->create();

        $response = $this->delete(route('category-groups.destroy', $categoryGroup));

        $response->assertNoContent();

        $this->assertModelMissing($categoryGroup);
    }
}
