<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\CustomerDocumentsType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CustomerDocumentsTypeController
 */
final class CustomerDocumentsTypeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $customerDocumentsTypes = CustomerDocumentsType::factory()->count(3)->create();

        $response = $this->get(route('customer-documents-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerDocumentsTypeController::class,
            'store',
            \App\Http\Requests\Api\v1\CustomerDocumentsTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('customer-documents-types.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $customerDocumentsTypes = CustomerDocumentsType::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $customerDocumentsTypes);
        $customerDocumentsType = $customerDocumentsTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $customerDocumentsType = CustomerDocumentsType::factory()->create();

        $response = $this->get(route('customer-documents-types.show', $customerDocumentsType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerDocumentsTypeController::class,
            'update',
            \App\Http\Requests\Api\v1\CustomerDocumentsTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $customerDocumentsType = CustomerDocumentsType::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('customer-documents-types.update', $customerDocumentsType), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $customerDocumentsType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $customerDocumentsType->code);
        $this->assertEquals($description, $customerDocumentsType->description);
        $this->assertEquals($is_active, $customerDocumentsType->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $customerDocumentsType = CustomerDocumentsType::factory()->create();

        $response = $this->delete(route('customer-documents-types.destroy', $customerDocumentsType));

        $response->assertNoContent();

        $this->assertModelMissing($customerDocumentsType);
    }
}
