<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CategoryController
 */
final class CategoryControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->get(route('categories.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CategoryController::class,
            'store',
            \App\Http\Requests\Api\v1\CategoryStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $category_parent_id = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->boolean();

        $response = $this->post(route('categories.store'), [
            'code' => $code,
            'description' => $description,
            'category_parent_id' => $category_parent_id,
            'is_active' => $is_active,
        ]);

        $categories = Category::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('category_parent_id', $category_parent_id)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $categories);
        $category = $categories->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $category = Category::factory()->create();

        $response = $this->get(route('categories.show', $category));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CategoryController::class,
            'update',
            \App\Http\Requests\Api\v1\CategoryUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $category = Category::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $category_parent_id = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->boolean();

        $response = $this->put(route('categories.update', $category), [
            'code' => $code,
            'description' => $description,
            'category_parent_id' => $category_parent_id,
            'is_active' => $is_active,
        ]);

        $category->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $category->code);
        $this->assertEquals($description, $category->description);
        $this->assertEquals($category_parent_id, $category->category_parent_id);
        $this->assertEquals($is_active, $category->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $category = Category::factory()->create();

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertNoContent();

        $this->assertModelMissing($category);
    }
}
