<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\DocumentsTypesProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\DocumentsTypesProviderController
 */
final class DocumentsTypesProviderControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $documentsTypesProviders = DocumentsTypesProvider::factory()->count(3)->create();

        $response = $this->get(route('documents-types-providers.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\DocumentsTypesProviderController::class,
            'store',
            \App\Http\Requests\Api\v1\DocumentsTypesProviderStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->numberBetween(-100000, 100000);

        $response = $this->post(route('documents-types-providers.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $documentsTypesProviders = DocumentsTypesProvider::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $documentsTypesProviders);
        $documentsTypesProvider = $documentsTypesProviders->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $documentsTypesProvider = DocumentsTypesProvider::factory()->create();

        $response = $this->get(route('documents-types-providers.show', $documentsTypesProvider));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\DocumentsTypesProviderController::class,
            'update',
            \App\Http\Requests\Api\v1\DocumentsTypesProviderUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $documentsTypesProvider = DocumentsTypesProvider::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->numberBetween(-100000, 100000);

        $response = $this->put(route('documents-types-providers.update', $documentsTypesProvider), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $documentsTypesProvider->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $documentsTypesProvider->code);
        $this->assertEquals($description, $documentsTypesProvider->description);
        $this->assertEquals($is_active, $documentsTypesProvider->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $documentsTypesProvider = DocumentsTypesProvider::factory()->create();

        $response = $this->delete(route('documents-types-providers.destroy', $documentsTypesProvider));

        $response->assertNoContent();

        $this->assertModelMissing($documentsTypesProvider);
    }
}
