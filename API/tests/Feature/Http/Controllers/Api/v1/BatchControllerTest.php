<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\BatchController
 */
final class BatchControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $batches = Batch::factory()->count(3)->create();

        $response = $this->get(route('batches.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\BatchController::class,
            'store',
            \App\Http\Requests\Api\v1\BatchStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $origen_code = fake()->numberBetween(-100000, 100000);
        $inventory_id = fake()->numberBetween(-100000, 100000);
        $incoming_date = Carbon::parse(fake()->date());
        $expiration_date = Carbon::parse(fake()->date());
        $initial_quantity = fake()->randomFloat(/** decimal_attributes **/);
        $available_quantity = fake()->randomFloat(/** decimal_attributes **/);
        $observations = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->post(route('batches.store'), [
            'code' => $code,
            'origen_code' => $origen_code,
            'inventory_id' => $inventory_id,
            'incoming_date' => $incoming_date->toDateString(),
            'expiration_date' => $expiration_date->toDateString(),
            'initial_quantity' => $initial_quantity,
            'available_quantity' => $available_quantity,
            'observations' => $observations,
            'is_active' => $is_active,
        ]);

        $batches = Batch::query()
            ->where('code', $code)
            ->where('origen_code', $origen_code)
            ->where('inventory_id', $inventory_id)
            ->where('incoming_date', $incoming_date)
            ->where('expiration_date', $expiration_date)
            ->where('initial_quantity', $initial_quantity)
            ->where('available_quantity', $available_quantity)
            ->where('observations', $observations)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $batches);
        $batch = $batches->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $batch = Batch::factory()->create();

        $response = $this->get(route('batches.show', $batch));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\BatchController::class,
            'update',
            \App\Http\Requests\Api\v1\BatchUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $batch = Batch::factory()->create();
        $code = fake()->word();
        $origen_code = fake()->numberBetween(-100000, 100000);
        $inventory_id = fake()->numberBetween(-100000, 100000);
        $incoming_date = Carbon::parse(fake()->date());
        $expiration_date = Carbon::parse(fake()->date());
        $initial_quantity = fake()->randomFloat(/** decimal_attributes **/);
        $available_quantity = fake()->randomFloat(/** decimal_attributes **/);
        $observations = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->put(route('batches.update', $batch), [
            'code' => $code,
            'origen_code' => $origen_code,
            'inventory_id' => $inventory_id,
            'incoming_date' => $incoming_date->toDateString(),
            'expiration_date' => $expiration_date->toDateString(),
            'initial_quantity' => $initial_quantity,
            'available_quantity' => $available_quantity,
            'observations' => $observations,
            'is_active' => $is_active,
        ]);

        $batch->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $batch->code);
        $this->assertEquals($origen_code, $batch->origen_code);
        $this->assertEquals($inventory_id, $batch->inventory_id);
        $this->assertEquals($incoming_date, $batch->incoming_date);
        $this->assertEquals($expiration_date, $batch->expiration_date);
        $this->assertEquals($initial_quantity, $batch->initial_quantity);
        $this->assertEquals($available_quantity, $batch->available_quantity);
        $this->assertEquals($observations, $batch->observations);
        $this->assertEquals($is_active, $batch->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $batch = Batch::factory()->create();

        $response = $this->delete(route('batches.destroy', $batch));

        $response->assertNoContent();

        $this->assertModelMissing($batch);
    }
}
