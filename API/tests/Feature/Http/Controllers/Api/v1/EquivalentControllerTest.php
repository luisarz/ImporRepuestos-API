<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Equivalent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\EquivalentController
 */
final class EquivalentControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $equivalents = Equivalent::factory()->count(3)->create();

        $response = $this->get(route('equivalents.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\EquivalentController::class,
            'store',
            \App\Http\Requests\Api\v1\EquivalentStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $product_id = fake()->numberBetween(-100000, 100000);
        $product_id_equivalent = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->boolean();

        $response = $this->post(route('equivalents.store'), [
            'product_id' => $product_id,
            'product_id_equivalent' => $product_id_equivalent,
            'is_active' => $is_active,
        ]);

        $equivalents = Equivalent::query()
            ->where('product_id', $product_id)
            ->where('product_id_equivalent', $product_id_equivalent)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $equivalents);
        $equivalent = $equivalents->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $equivalent = Equivalent::factory()->create();

        $response = $this->get(route('equivalents.show', $equivalent));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\EquivalentController::class,
            'update',
            \App\Http\Requests\Api\v1\EquivalentUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $equivalent = Equivalent::factory()->create();
        $product_id = fake()->numberBetween(-100000, 100000);
        $product_id_equivalent = fake()->numberBetween(-100000, 100000);
        $is_active = fake()->boolean();

        $response = $this->put(route('equivalents.update', $equivalent), [
            'product_id' => $product_id,
            'product_id_equivalent' => $product_id_equivalent,
            'is_active' => $is_active,
        ]);

        $equivalent->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($product_id, $equivalent->product_id);
        $this->assertEquals($product_id_equivalent, $equivalent->product_id_equivalent);
        $this->assertEquals($is_active, $equivalent->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $equivalent = Equivalent::factory()->create();

        $response = $this->delete(route('equivalents.destroy', $equivalent));

        $response->assertNoContent();

        $this->assertModelMissing($equivalent);
    }
}
