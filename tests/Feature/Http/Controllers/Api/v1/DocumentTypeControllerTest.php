<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\DocumentTypeController
 */
final class DocumentTypeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $documentTypes = DocumentType::factory()->count(3)->create();

        $response = $this->get(route('document-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\DocumentTypeController::class,
            'store',
            \App\Http\Requests\Api\v1\DocumentTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = fake()->name();
        $is_active = fake()->boolean();

        $response = $this->post(route('document-types.store'), [
            'name' => $name,
            'is_active' => $is_active,
        ]);

        $documentTypes = DocumentType::query()
            ->where('name', $name)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $documentTypes);
        $documentType = $documentTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $documentType = DocumentType::factory()->create();

        $response = $this->get(route('document-types.show', $documentType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\DocumentTypeController::class,
            'update',
            \App\Http\Requests\Api\v1\DocumentTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $documentType = DocumentType::factory()->create();
        $name = fake()->name();
        $is_active = fake()->boolean();

        $response = $this->put(route('document-types.update', $documentType), [
            'name' => $name,
            'is_active' => $is_active,
        ]);

        $documentType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $documentType->name);
        $this->assertEquals($is_active, $documentType->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $documentType = DocumentType::factory()->create();

        $response = $this->delete(route('document-types.destroy', $documentType));

        $response->assertNoContent();

        $this->assertModelMissing($documentType);
    }
}
