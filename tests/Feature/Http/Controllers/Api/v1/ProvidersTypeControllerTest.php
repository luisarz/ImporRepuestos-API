<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\ProvidersType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\ProvidersTypeController
 */
final class ProvidersTypeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $providersTypes = ProvidersType::factory()->count(3)->create();

        $response = $this->get(route('providers-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProvidersTypeController::class,
            'store',
            \App\Http\Requests\Api\v1\ProvidersTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('providers-types.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $providersTypes = ProvidersType::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $providersTypes);
        $providersType = $providersTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $providersType = ProvidersType::factory()->create();

        $response = $this->get(route('providers-types.show', $providersType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProvidersTypeController::class,
            'update',
            \App\Http\Requests\Api\v1\ProvidersTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $providersType = ProvidersType::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('providers-types.update', $providersType), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $providersType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $providersType->code);
        $this->assertEquals($description, $providersType->description);
        $this->assertEquals($is_active, $providersType->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $providersType = ProvidersType::factory()->create();

        $response = $this->delete(route('providers-types.destroy', $providersType));

        $response->assertNoContent();

        $this->assertModelMissing($providersType);
    }
}
