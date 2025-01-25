<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\InventoriesBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\InventoriesBatchController
 */
final class InventoriesBatchControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $inventoriesBatches = InventoriesBatch::factory()->count(3)->create();

        $response = $this->get(route('inventories-batches.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\InventoriesBatchController::class,
            'store',
            \App\Http\Requests\Api\v1\InventoriesBatchStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $id_inventory = fake()->numberBetween(-100000, 100000);
        $id_batch = fake()->numberBetween(-100000, 100000);
        $quantity = fake()->randomFloat(/** decimal_attributes **/);
        $operation_date = Carbon::parse(fake()->dateTime());

        $response = $this->post(route('inventories-batches.store'), [
            'id_inventory' => $id_inventory,
            'id_batch' => $id_batch,
            'quantity' => $quantity,
            'operation_date' => $operation_date->toDateTimeString(),
        ]);

        $inventoriesBatches = InventoriesBatch::query()
            ->where('id_inventory', $id_inventory)
            ->where('id_batch', $id_batch)
            ->where('quantity', $quantity)
            ->where('operation_date', $operation_date)
            ->get();
        $this->assertCount(1, $inventoriesBatches);
        $inventoriesBatch = $inventoriesBatches->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $inventoriesBatch = InventoriesBatch::factory()->create();

        $response = $this->get(route('inventories-batches.show', $inventoriesBatch));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\InventoriesBatchController::class,
            'update',
            \App\Http\Requests\Api\v1\InventoriesBatchUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $inventoriesBatch = InventoriesBatch::factory()->create();
        $id_inventory = fake()->numberBetween(-100000, 100000);
        $id_batch = fake()->numberBetween(-100000, 100000);
        $quantity = fake()->randomFloat(/** decimal_attributes **/);
        $operation_date = Carbon::parse(fake()->dateTime());

        $response = $this->put(route('inventories-batches.update', $inventoriesBatch), [
            'id_inventory' => $id_inventory,
            'id_batch' => $id_batch,
            'quantity' => $quantity,
            'operation_date' => $operation_date->toDateTimeString(),
        ]);

        $inventoriesBatch->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($id_inventory, $inventoriesBatch->id_inventory);
        $this->assertEquals($id_batch, $inventoriesBatch->id_batch);
        $this->assertEquals($quantity, $inventoriesBatch->quantity);
        $this->assertEquals($operation_date, $inventoriesBatch->operation_date);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $inventoriesBatch = InventoriesBatch::factory()->create();

        $response = $this->delete(route('inventories-batches.destroy', $inventoriesBatch));

        $response->assertNoContent();

        $this->assertModelMissing($inventoriesBatch);
    }
}
