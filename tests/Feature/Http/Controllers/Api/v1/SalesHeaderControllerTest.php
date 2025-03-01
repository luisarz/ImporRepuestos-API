<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\SalesHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\SalesHeaderController
 */
final class SalesHeaderControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $salesHeaders = SalesHeader::factory()->count(3)->create();

        $response = $this->get(route('sales-headers.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\SalesHeaderController::class,
            'store',
            \App\Http\Requests\Api\v1\SalesHeaderStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $cashbox_open_id = fake()->numberBetween(-100000, 100000);
        $sale_date = Carbon::parse(fake()->dateTime());
        $warehouse_id = fake()->numberBetween(-100000, 100000);
        $document_type_id = fake()->numberBetween(-100000, 100000);
        $document_internal_number = fake()->numberBetween(-100000, 100000);
        $seller_id = fake()->numberBetween(-100000, 100000);
        $customer_id = fake()->numberBetween(-100000, 100000);
        $operation_condition_id = fake()->numberBetween(-100000, 100000);
        $sale_status = fake()->randomElement(/** enum_attributes **/);
        $net_amount = fake()->randomFloat(/** decimal_attributes **/);
        $tax = fake()->randomFloat(/** decimal_attributes **/);
        $discount = fake()->randomFloat(/** decimal_attributes **/);
        $have_retention = fake()->boolean();
        $retention = fake()->randomFloat(/** decimal_attributes **/);
        $sale_total = fake()->randomFloat(/** decimal_attributes **/);
        $payment_status = fake()->numberBetween(-100000, 100000);
        $is_order = fake()->boolean();
        $is_order_closed_without_invoiced = fake()->boolean();
        $is_invoiced_order = fake()->boolean();
        $discount_percentage = fake()->randomFloat(/** decimal_attributes **/);
        $discount_money = fake()->randomFloat(/** decimal_attributes **/);
        $total_order_after_discount = fake()->randomFloat(/** decimal_attributes **/);
        $is_active = fake()->boolean();

        $response = $this->post(route('sales-headers.store'), [
            'cashbox_open_id' => $cashbox_open_id,
            'sale_date' => $sale_date->toDateTimeString(),
            'warehouse_id' => $warehouse_id,
            'document_type_id' => $document_type_id,
            'document_internal_number' => $document_internal_number,
            'seller_id' => $seller_id,
            'customer_id' => $customer_id,
            'operation_condition_id' => $operation_condition_id,
            'sale_status' => $sale_status,
            'net_amount' => $net_amount,
            'tax' => $tax,
            'discount' => $discount,
            'have_retention' => $have_retention,
            'retention' => $retention,
            'sale_total' => $sale_total,
            'payment_status' => $payment_status,
            'is_order' => $is_order,
            'is_order_closed_without_invoiced' => $is_order_closed_without_invoiced,
            'is_invoiced_order' => $is_invoiced_order,
            'discount_percentage' => $discount_percentage,
            'discount_money' => $discount_money,
            'total_order_after_discount' => $total_order_after_discount,
            'is_active' => $is_active,
        ]);

        $salesHeaders = SalesHeader::query()
            ->where('cashbox_open_id', $cashbox_open_id)
            ->where('sale_date', $sale_date)
            ->where('warehouse_id', $warehouse_id)
            ->where('document_type_id', $document_type_id)
            ->where('document_internal_number', $document_internal_number)
            ->where('seller_id', $seller_id)
            ->where('customer_id', $customer_id)
            ->where('operation_condition_id', $operation_condition_id)
            ->where('sale_status', $sale_status)
            ->where('net_amount', $net_amount)
            ->where('tax', $tax)
            ->where('discount', $discount)
            ->where('have_retention', $have_retention)
            ->where('retention', $retention)
            ->where('sale_total', $sale_total)
            ->where('payment_status', $payment_status)
            ->where('is_order', $is_order)
            ->where('is_order_closed_without_invoiced', $is_order_closed_without_invoiced)
            ->where('is_invoiced_order', $is_invoiced_order)
            ->where('discount_percentage', $discount_percentage)
            ->where('discount_money', $discount_money)
            ->where('total_order_after_discount', $total_order_after_discount)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $salesHeaders);
        $salesHeader = $salesHeaders->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $salesHeader = SalesHeader::factory()->create();

        $response = $this->get(route('sales-headers.show', $salesHeader));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\SalesHeaderController::class,
            'update',
            \App\Http\Requests\Api\v1\SalesHeaderUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $salesHeader = SalesHeader::factory()->create();
        $cashbox_open_id = fake()->numberBetween(-100000, 100000);
        $sale_date = Carbon::parse(fake()->dateTime());
        $warehouse_id = fake()->numberBetween(-100000, 100000);
        $document_type_id = fake()->numberBetween(-100000, 100000);
        $document_internal_number = fake()->numberBetween(-100000, 100000);
        $seller_id = fake()->numberBetween(-100000, 100000);
        $customer_id = fake()->numberBetween(-100000, 100000);
        $operation_condition_id = fake()->numberBetween(-100000, 100000);
        $sale_status = fake()->randomElement(/** enum_attributes **/);
        $net_amount = fake()->randomFloat(/** decimal_attributes **/);
        $tax = fake()->randomFloat(/** decimal_attributes **/);
        $discount = fake()->randomFloat(/** decimal_attributes **/);
        $have_retention = fake()->boolean();
        $retention = fake()->randomFloat(/** decimal_attributes **/);
        $sale_total = fake()->randomFloat(/** decimal_attributes **/);
        $payment_status = fake()->numberBetween(-100000, 100000);
        $is_order = fake()->boolean();
        $is_order_closed_without_invoiced = fake()->boolean();
        $is_invoiced_order = fake()->boolean();
        $discount_percentage = fake()->randomFloat(/** decimal_attributes **/);
        $discount_money = fake()->randomFloat(/** decimal_attributes **/);
        $total_order_after_discount = fake()->randomFloat(/** decimal_attributes **/);
        $is_active = fake()->boolean();

        $response = $this->put(route('sales-headers.update', $salesHeader), [
            'cashbox_open_id' => $cashbox_open_id,
            'sale_date' => $sale_date->toDateTimeString(),
            'warehouse_id' => $warehouse_id,
            'document_type_id' => $document_type_id,
            'document_internal_number' => $document_internal_number,
            'seller_id' => $seller_id,
            'customer_id' => $customer_id,
            'operation_condition_id' => $operation_condition_id,
            'sale_status' => $sale_status,
            'net_amount' => $net_amount,
            'tax' => $tax,
            'discount' => $discount,
            'have_retention' => $have_retention,
            'retention' => $retention,
            'sale_total' => $sale_total,
            'payment_status' => $payment_status,
            'is_order' => $is_order,
            'is_order_closed_without_invoiced' => $is_order_closed_without_invoiced,
            'is_invoiced_order' => $is_invoiced_order,
            'discount_percentage' => $discount_percentage,
            'discount_money' => $discount_money,
            'total_order_after_discount' => $total_order_after_discount,
            'is_active' => $is_active,
        ]);

        $salesHeader->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($cashbox_open_id, $salesHeader->cashbox_open_id);
        $this->assertEquals($sale_date, $salesHeader->sale_date);
        $this->assertEquals($warehouse_id, $salesHeader->warehouse_id);
        $this->assertEquals($document_type_id, $salesHeader->document_type_id);
        $this->assertEquals($document_internal_number, $salesHeader->document_internal_number);
        $this->assertEquals($seller_id, $salesHeader->seller_id);
        $this->assertEquals($customer_id, $salesHeader->customer_id);
        $this->assertEquals($operation_condition_id, $salesHeader->operation_condition_id);
        $this->assertEquals($sale_status, $salesHeader->sale_status);
        $this->assertEquals($net_amount, $salesHeader->net_amount);
        $this->assertEquals($tax, $salesHeader->tax);
        $this->assertEquals($discount, $salesHeader->discount);
        $this->assertEquals($have_retention, $salesHeader->have_retention);
        $this->assertEquals($retention, $salesHeader->retention);
        $this->assertEquals($sale_total, $salesHeader->sale_total);
        $this->assertEquals($payment_status, $salesHeader->payment_status);
        $this->assertEquals($is_order, $salesHeader->is_order);
        $this->assertEquals($is_order_closed_without_invoiced, $salesHeader->is_order_closed_without_invoiced);
        $this->assertEquals($is_invoiced_order, $salesHeader->is_invoiced_order);
        $this->assertEquals($discount_percentage, $salesHeader->discount_percentage);
        $this->assertEquals($discount_money, $salesHeader->discount_money);
        $this->assertEquals($total_order_after_discount, $salesHeader->total_order_after_discount);
        $this->assertEquals($is_active, $salesHeader->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $salesHeader = SalesHeader::factory()->create();

        $response = $this->delete(route('sales-headers.destroy', $salesHeader));

        $response->assertNoContent();

        $this->assertModelMissing($salesHeader);
    }
}
