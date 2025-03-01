<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\ProductController
 */
final class ProductControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProductController::class,
            'store',
            \App\Http\Requests\Api\v1\ProductStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $original_code = fake()->word();
        $brand_id = fake()->numberBetween(-100000, 100000);
        $category_id = fake()->numberBetween(-100000, 100000);
        $description_measurement_id = fake()->word();
        $is_active = fake()->boolean();
        $is_taxed = fake()->boolean();
        $is_service = fake()->boolean();

        $response = $this->post(route('products.store'), [
            'code' => $code,
            'original_code' => $original_code,
            'brand_id' => $brand_id,
            'category_id' => $category_id,
            'description_measurement_id' => $description_measurement_id,
            'is_active' => $is_active,
            'is_taxed' => $is_taxed,
            'is_service' => $is_service,
        ]);

        $products = Product::query()
            ->where('code', $code)
            ->where('original_code', $original_code)
            ->where('brand_id', $brand_id)
            ->where('category_id', $category_id)
            ->where('description_measurement_id', $description_measurement_id)
            ->where('is_active', $is_active)
            ->where('is_taxed', $is_taxed)
            ->where('is_service', $is_service)
            ->get();
        $this->assertCount(1, $products);
        $product = $products->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProductController::class,
            'update',
            \App\Http\Requests\Api\v1\ProductUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $product = Product::factory()->create();
        $code = fake()->word();
        $original_code = fake()->word();
        $brand_id = fake()->numberBetween(-100000, 100000);
        $category_id = fake()->numberBetween(-100000, 100000);
        $description_measurement_id = fake()->word();
        $is_active = fake()->boolean();
        $is_taxed = fake()->boolean();
        $is_service = fake()->boolean();

        $response = $this->put(route('products.update', $product), [
            'code' => $code,
            'original_code' => $original_code,
            'brand_id' => $brand_id,
            'category_id' => $category_id,
            'description_measurement_id' => $description_measurement_id,
            'is_active' => $is_active,
            'is_taxed' => $is_taxed,
            'is_service' => $is_service,
        ]);

        $product->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $product->code);
        $this->assertEquals($original_code, $product->original_code);
        $this->assertEquals($brand_id, $product->brand_id);
        $this->assertEquals($category_id, $product->category_id);
        $this->assertEquals($description_measurement_id, $product->description_measurement_id);
        $this->assertEquals($is_active, $product->is_active);
        $this->assertEquals($is_taxed, $product->is_taxed);
        $this->assertEquals($is_service, $product->is_service);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertNoContent();

        $this->assertModelMissing($product);
    }
}
