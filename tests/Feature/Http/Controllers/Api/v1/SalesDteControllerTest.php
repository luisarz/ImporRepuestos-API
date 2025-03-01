<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\SalesDte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\SalesDteController
 */
final class SalesDteControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $salesDtes = SalesDte::factory()->count(3)->create();

        $response = $this->get(route('sales-dtes.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\SalesDteController::class,
            'store',
            \App\Http\Requests\Api\v1\SalesDteStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $sale_id = fake()->numberBetween(-100000, 100000);
        $is_dte = fake()->boolean();
        $generation_code = fake()->numberBetween(-100000, 100000);
        $billing_model = fake()->numberBetween(-100000, 100000);
        $transmition_type = fake()->numberBetween(-100000, 100000);
        $receipt_stamp = fake()->word();

        $response = $this->post(route('sales-dtes.store'), [
            'sale_id' => $sale_id,
            'is_dte' => $is_dte,
            'generation_code' => $generation_code,
            'billing_model' => $billing_model,
            'transmition_type' => $transmition_type,
            'receipt_stamp' => $receipt_stamp,
        ]);

        $salesDtes = SalesDte::query()
            ->where('sale_id', $sale_id)
            ->where('is_dte', $is_dte)
            ->where('generation_code', $generation_code)
            ->where('billing_model', $billing_model)
            ->where('transmition_type', $transmition_type)
            ->where('receipt_stamp', $receipt_stamp)
            ->get();
        $this->assertCount(1, $salesDtes);
        $salesDte = $salesDtes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $salesDte = SalesDte::factory()->create();

        $response = $this->get(route('sales-dtes.show', $salesDte));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\SalesDteController::class,
            'update',
            \App\Http\Requests\Api\v1\SalesDteUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $salesDte = SalesDte::factory()->create();
        $sale_id = fake()->numberBetween(-100000, 100000);
        $is_dte = fake()->boolean();
        $generation_code = fake()->numberBetween(-100000, 100000);
        $billing_model = fake()->numberBetween(-100000, 100000);
        $transmition_type = fake()->numberBetween(-100000, 100000);
        $receipt_stamp = fake()->word();

        $response = $this->put(route('sales-dtes.update', $salesDte), [
            'sale_id' => $sale_id,
            'is_dte' => $is_dte,
            'generation_code' => $generation_code,
            'billing_model' => $billing_model,
            'transmition_type' => $transmition_type,
            'receipt_stamp' => $receipt_stamp,
        ]);

        $salesDte->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($sale_id, $salesDte->sale_id);
        $this->assertEquals($is_dte, $salesDte->is_dte);
        $this->assertEquals($generation_code, $salesDte->generation_code);
        $this->assertEquals($billing_model, $salesDte->billing_model);
        $this->assertEquals($transmition_type, $salesDte->transmition_type);
        $this->assertEquals($receipt_stamp, $salesDte->receipt_stamp);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $salesDte = SalesDte::factory()->create();

        $response = $this->delete(route('sales-dtes.destroy', $salesDte));

        $response->assertNoContent();

        $this->assertModelMissing($salesDte);
    }
}
