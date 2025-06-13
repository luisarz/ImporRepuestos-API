<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\OperationCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\OperationConditionController
 */
final class OperationConditionControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $operationConditions = OperationCondition::factory()->count(3)->create();

        $response = $this->get(route('operation-conditions.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\OperationConditionController::class,
            'store',
            \App\Http\Requests\Api\v1\OperationConditionStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $name = fake()->name();
        $is_active = fake()->boolean();

        $response = $this->post(route('operation-conditions.store'), [
            'code' => $code,
            'name' => $name,
            'is_active' => $is_active,
        ]);

        $operationConditions = OperationCondition::query()
            ->where('code', $code)
            ->where('name', $name)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $operationConditions);
        $operationCondition = $operationConditions->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $operationCondition = OperationCondition::factory()->create();

        $response = $this->get(route('operation-conditions.show', $operationCondition));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\OperationConditionController::class,
            'update',
            \App\Http\Requests\Api\v1\OperationConditionUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $operationCondition = OperationCondition::factory()->create();
        $code = fake()->word();
        $name = fake()->name();
        $is_active = fake()->boolean();

        $response = $this->put(route('operation-conditions.update', $operationCondition), [
            'code' => $code,
            'name' => $name,
            'is_active' => $is_active,
        ]);

        $operationCondition->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $operationCondition->code);
        $this->assertEquals($name, $operationCondition->name);
        $this->assertEquals($is_active, $operationCondition->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $operationCondition = OperationCondition::factory()->create();

        $response = $this->delete(route('operation-conditions.destroy', $operationCondition));

        $response->assertNoContent();

        $this->assertModelMissing($operationCondition);
    }
}
