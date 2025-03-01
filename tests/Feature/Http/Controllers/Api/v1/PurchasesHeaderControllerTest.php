<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\PurchasesHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\PurchasesHeaderController
 */
final class PurchasesHeaderControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $purchasesHeaders = PurchasesHeader::factory()->count(3)->create();

        $response = $this->get(route('purchases-headers.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PurchasesHeaderController::class,
            'store',
            \App\Http\Requests\Api\v1\PurchasesHeaderStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $warehouse = fake()->numberBetween(-100000, 100000);
        $provider_id = fake()->numberBetween(-100000, 100000);
        $purchcase_date = Carbon::parse(fake()->date());
        $serie = fake()->word();
        $purchase_number = fake()->word();
        $resolution = fake()->word();
        $purchase_type = fake()->numberBetween(-100000, 100000);
        $paymen_method = fake()->randomElement(/** enum_attributes **/);
        $payment_status = fake()->randomElement(/** enum_attributes **/);
        $net_amount = fake()->randomFloat(/** decimal_attributes **/);
        $tax_amount = fake()->randomFloat(/** decimal_attributes **/);
        $retention_amount = fake()->randomFloat(/** decimal_attributes **/);
        $total_purchase = fake()->randomFloat(/** decimal_attributes **/);
        $employee_id = fake()->numberBetween(-100000, 100000);
        $status_purchase = fake()->randomElement(/** enum_attributes **/);

        $response = $this->post(route('purchases-headers.store'), [
            'warehouse' => $warehouse,
            'provider_id' => $provider_id,
            'purchcase_date' => $purchcase_date->toDateString(),
            'serie' => $serie,
            'purchase_number' => $purchase_number,
            'resolution' => $resolution,
            'purchase_type' => $purchase_type,
            'paymen_method' => $paymen_method,
            'payment_status' => $payment_status,
            'net_amount' => $net_amount,
            'tax_amount' => $tax_amount,
            'retention_amount' => $retention_amount,
            'total_purchase' => $total_purchase,
            'employee_id' => $employee_id,
            'status_purchase' => $status_purchase,
        ]);

        $purchasesHeaders = PurchasesHeader::query()
            ->where('warehouse', $warehouse)
            ->where('provider_id', $provider_id)
            ->where('purchcase_date', $purchcase_date)
            ->where('serie', $serie)
            ->where('purchase_number', $purchase_number)
            ->where('resolution', $resolution)
            ->where('purchase_type', $purchase_type)
            ->where('paymen_method', $paymen_method)
            ->where('payment_status', $payment_status)
            ->where('net_amount', $net_amount)
            ->where('tax_amount', $tax_amount)
            ->where('retention_amount', $retention_amount)
            ->where('total_purchase', $total_purchase)
            ->where('employee_id', $employee_id)
            ->where('status_purchase', $status_purchase)
            ->get();
        $this->assertCount(1, $purchasesHeaders);
        $purchasesHeader = $purchasesHeaders->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $purchasesHeader = PurchasesHeader::factory()->create();

        $response = $this->get(route('purchases-headers.show', $purchasesHeader));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PurchasesHeaderController::class,
            'update',
            \App\Http\Requests\Api\v1\PurchasesHeaderUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $purchasesHeader = PurchasesHeader::factory()->create();
        $warehouse = fake()->numberBetween(-100000, 100000);
        $provider_id = fake()->numberBetween(-100000, 100000);
        $purchcase_date = Carbon::parse(fake()->date());
        $serie = fake()->word();
        $purchase_number = fake()->word();
        $resolution = fake()->word();
        $purchase_type = fake()->numberBetween(-100000, 100000);
        $paymen_method = fake()->randomElement(/** enum_attributes **/);
        $payment_status = fake()->randomElement(/** enum_attributes **/);
        $net_amount = fake()->randomFloat(/** decimal_attributes **/);
        $tax_amount = fake()->randomFloat(/** decimal_attributes **/);
        $retention_amount = fake()->randomFloat(/** decimal_attributes **/);
        $total_purchase = fake()->randomFloat(/** decimal_attributes **/);
        $employee_id = fake()->numberBetween(-100000, 100000);
        $status_purchase = fake()->randomElement(/** enum_attributes **/);

        $response = $this->put(route('purchases-headers.update', $purchasesHeader), [
            'warehouse' => $warehouse,
            'provider_id' => $provider_id,
            'purchcase_date' => $purchcase_date->toDateString(),
            'serie' => $serie,
            'purchase_number' => $purchase_number,
            'resolution' => $resolution,
            'purchase_type' => $purchase_type,
            'paymen_method' => $paymen_method,
            'payment_status' => $payment_status,
            'net_amount' => $net_amount,
            'tax_amount' => $tax_amount,
            'retention_amount' => $retention_amount,
            'total_purchase' => $total_purchase,
            'employee_id' => $employee_id,
            'status_purchase' => $status_purchase,
        ]);

        $purchasesHeader->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($warehouse, $purchasesHeader->warehouse);
        $this->assertEquals($provider_id, $purchasesHeader->provider_id);
        $this->assertEquals($purchcase_date, $purchasesHeader->purchcase_date);
        $this->assertEquals($serie, $purchasesHeader->serie);
        $this->assertEquals($purchase_number, $purchasesHeader->purchase_number);
        $this->assertEquals($resolution, $purchasesHeader->resolution);
        $this->assertEquals($purchase_type, $purchasesHeader->purchase_type);
        $this->assertEquals($paymen_method, $purchasesHeader->paymen_method);
        $this->assertEquals($payment_status, $purchasesHeader->payment_status);
        $this->assertEquals($net_amount, $purchasesHeader->net_amount);
        $this->assertEquals($tax_amount, $purchasesHeader->tax_amount);
        $this->assertEquals($retention_amount, $purchasesHeader->retention_amount);
        $this->assertEquals($total_purchase, $purchasesHeader->total_purchase);
        $this->assertEquals($employee_id, $purchasesHeader->employee_id);
        $this->assertEquals($status_purchase, $purchasesHeader->status_purchase);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $purchasesHeader = PurchasesHeader::factory()->create();

        $response = $this->delete(route('purchases-headers.destroy', $purchasesHeader));

        $response->assertNoContent();

        $this->assertModelMissing($purchasesHeader);
    }
}
