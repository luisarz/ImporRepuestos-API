<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\SalePaymentDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\SalePaymentDetailController
 */
final class SalePaymentDetailControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $salePaymentDetails = SalePaymentDetail::factory()->count(3)->create();

        $response = $this->get(route('sale-payment-details.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\SalePaymentDetailController::class,
            'store',
            \App\Http\Requests\Api\v1\SalePaymentDetailStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $sale_id = fake()->numberBetween(-100000, 100000);
        $payment_method_id = fake()->numberBetween(-100000, 100000);
        $casher_id = fake()->numberBetween(-100000, 100000);
        $payment_amount = fake()->randomFloat(/** decimal_attributes **/);
        $actual_balance = fake()->randomFloat(/** decimal_attributes **/);
        $bank_account_id = fake()->numberBetween(-100000, 100000);
        $reference = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->post(route('sale-payment-details.store'), [
            'sale_id' => $sale_id,
            'payment_method_id' => $payment_method_id,
            'casher_id' => $casher_id,
            'payment_amount' => $payment_amount,
            'actual_balance' => $actual_balance,
            'bank_account_id' => $bank_account_id,
            'reference' => $reference,
            'is_active' => $is_active,
        ]);

        $salePaymentDetails = SalePaymentDetail::query()
            ->where('sale_id', $sale_id)
            ->where('payment_method_id', $payment_method_id)
            ->where('casher_id', $casher_id)
            ->where('payment_amount', $payment_amount)
            ->where('actual_balance', $actual_balance)
            ->where('bank_account_id', $bank_account_id)
            ->where('reference', $reference)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $salePaymentDetails);
        $salePaymentDetail = $salePaymentDetails->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $salePaymentDetail = SalePaymentDetail::factory()->create();

        $response = $this->get(route('sale-payment-details.show', $salePaymentDetail));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\SalePaymentDetailController::class,
            'update',
            \App\Http\Requests\Api\v1\SalePaymentDetailUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $salePaymentDetail = SalePaymentDetail::factory()->create();
        $sale_id = fake()->numberBetween(-100000, 100000);
        $payment_method_id = fake()->numberBetween(-100000, 100000);
        $casher_id = fake()->numberBetween(-100000, 100000);
        $payment_amount = fake()->randomFloat(/** decimal_attributes **/);
        $actual_balance = fake()->randomFloat(/** decimal_attributes **/);
        $bank_account_id = fake()->numberBetween(-100000, 100000);
        $reference = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->put(route('sale-payment-details.update', $salePaymentDetail), [
            'sale_id' => $sale_id,
            'payment_method_id' => $payment_method_id,
            'casher_id' => $casher_id,
            'payment_amount' => $payment_amount,
            'actual_balance' => $actual_balance,
            'bank_account_id' => $bank_account_id,
            'reference' => $reference,
            'is_active' => $is_active,
        ]);

        $salePaymentDetail->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($sale_id, $salePaymentDetail->sale_id);
        $this->assertEquals($payment_method_id, $salePaymentDetail->payment_method_id);
        $this->assertEquals($casher_id, $salePaymentDetail->casher_id);
        $this->assertEquals($payment_amount, $salePaymentDetail->payment_amount);
        $this->assertEquals($actual_balance, $salePaymentDetail->actual_balance);
        $this->assertEquals($bank_account_id, $salePaymentDetail->bank_account_id);
        $this->assertEquals($reference, $salePaymentDetail->reference);
        $this->assertEquals($is_active, $salePaymentDetail->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $salePaymentDetail = SalePaymentDetail::factory()->create();

        $response = $this->delete(route('sale-payment-details.destroy', $salePaymentDetail));

        $response->assertNoContent();

        $this->assertModelMissing($salePaymentDetail);
    }
}
