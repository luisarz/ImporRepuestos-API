<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\HistoryDte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\HistoryDteController
 */
final class HistoryDteControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $historyDtes = HistoryDte::factory()->count(3)->create();

        $response = $this->get(route('history-dtes.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\HistoryDteController::class,
            'store',
            \App\Http\Requests\Api\v1\HistoryDteStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $sale_dte_id = fake()->numberBetween(-100000, 100000);
        $status = fake()->randomElement(/** enum_attributes **/);

        $response = $this->post(route('history-dtes.store'), [
            'sale_dte_id' => $sale_dte_id,
            'status' => $status,
        ]);

        $historyDtes = HistoryDte::query()
            ->where('sale_dte_id', $sale_dte_id)
            ->where('status', $status)
            ->get();
        $this->assertCount(1, $historyDtes);
        $historyDte = $historyDtes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $historyDte = HistoryDte::factory()->create();

        $response = $this->get(route('history-dtes.show', $historyDte));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\HistoryDteController::class,
            'update',
            \App\Http\Requests\Api\v1\HistoryDteUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $historyDte = HistoryDte::factory()->create();
        $sale_dte_id = fake()->numberBetween(-100000, 100000);
        $status = fake()->randomElement(/** enum_attributes **/);

        $response = $this->put(route('history-dtes.update', $historyDte), [
            'sale_dte_id' => $sale_dte_id,
            'status' => $status,
        ]);

        $historyDte->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($sale_dte_id, $historyDte->sale_dte_id);
        $this->assertEquals($status, $historyDte->status);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $historyDte = HistoryDte::factory()->create();

        $response = $this->delete(route('history-dtes.destroy', $historyDte));

        $response->assertNoContent();

        $this->assertModelMissing($historyDte);
    }
}
