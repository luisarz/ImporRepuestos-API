<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\CustomerType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CustomerTypeController
 */
final class CustomerTypeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $customerTypes = CustomerType::factory()->count(3)->create();

        $response = $this->get(route('customer-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerTypeController::class,
            'store',
            \App\Http\Requests\Api\v1\CustomerTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('customer-types.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $customerTypes = CustomerType::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $customerTypes);
        $customerType = $customerTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $customerType = CustomerType::factory()->create();

        $response = $this->get(route('customer-types.show', $customerType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerTypeController::class,
            'update',
            \App\Http\Requests\Api\v1\CustomerTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $customerType = CustomerType::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('customer-types.update', $customerType), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $customerType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $customerType->code);
        $this->assertEquals($description, $customerType->description);
        $this->assertEquals($is_active, $customerType->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $customerType = CustomerType::factory()->create();

        $response = $this->delete(route('customer-types.destroy', $customerType));

        $response->assertNoContent();

        $this->assertModelMissing($customerType);
    }
}
