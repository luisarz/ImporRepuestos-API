<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\BrandController
 */
final class BrandControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $brands = Brand::factory()->count(3)->create();

        $response = $this->get(route('brands.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\BrandController::class,
            'store',
            \App\Http\Requests\Api\v1\BrandStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $image = fake()->;
        $is_active = fake()->boolean();

        $response = $this->post(route('brands.store'), [
            'code' => $code,
            'description' => $description,
            'image' => $image,
            'is_active' => $is_active,
        ]);

        $brands = Brand::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('image', $image)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $brands);
        $brand = $brands->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->get(route('brands.show', $brand));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\BrandController::class,
            'update',
            \App\Http\Requests\Api\v1\BrandUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $brand = Brand::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $image = fake()->;
        $is_active = fake()->boolean();

        $response = $this->put(route('brands.update', $brand), [
            'code' => $code,
            'description' => $description,
            'image' => $image,
            'is_active' => $is_active,
        ]);

        $brand->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $brand->code);
        $this->assertEquals($description, $brand->description);
        $this->assertEquals($image, $brand->image);
        $this->assertEquals($is_active, $brand->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->delete(route('brands.destroy', $brand));

        $response->assertNoContent();

        $this->assertModelMissing($brand);
    }
}
