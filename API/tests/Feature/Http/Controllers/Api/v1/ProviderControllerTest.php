<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\ProviderController
 */
final class ProviderControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $providers = Provider::factory()->count(3)->create();

        $response = $this->get(route('providers.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProviderController::class,
            'store',
            \App\Http\Requests\Api\v1\ProviderStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $legal_name = fake()->word();
        $comercial_name = fake()->word();
        $document_type_id = fake()->numberBetween(-100000, 100000);
        $document_number = fake()->word();
        $economic_activity_id = fake()->numberBetween(-100000, 100000);
        $provider_type_id = fake()->numberBetween(-100000, 100000);
        $payment_type_id = fake()->numberBetween(-100000, 100000);
        $credit_days = fake()->numberBetween(-10000, 10000);
        $credit_limit = fake()->randomFloat(/** decimal_attributes **/);
        $debit_balance = fake()->randomFloat(/** decimal_attributes **/);
        $last_purchase = Carbon::parse(fake()->date());
        $decimal_purchase = fake()->numberBetween(-10000, 10000);
        $is_active = fake()->boolean();

        $response = $this->post(route('providers.store'), [
            'legal_name' => $legal_name,
            'comercial_name' => $comercial_name,
            'document_type_id' => $document_type_id,
            'document_number' => $document_number,
            'economic_activity_id' => $economic_activity_id,
            'provider_type_id' => $provider_type_id,
            'payment_type_id' => $payment_type_id,
            'credit_days' => $credit_days,
            'credit_limit' => $credit_limit,
            'debit_balance' => $debit_balance,
            'last_purchase' => $last_purchase->toDateString(),
            'decimal_purchase' => $decimal_purchase,
            'is_active' => $is_active,
        ]);

        $providers = Provider::query()
            ->where('legal_name', $legal_name)
            ->where('comercial_name', $comercial_name)
            ->where('document_type_id', $document_type_id)
            ->where('document_number', $document_number)
            ->where('economic_activity_id', $economic_activity_id)
            ->where('provider_type_id', $provider_type_id)
            ->where('payment_type_id', $payment_type_id)
            ->where('credit_days', $credit_days)
            ->where('credit_limit', $credit_limit)
            ->where('debit_balance', $debit_balance)
            ->where('last_purchase', $last_purchase)
            ->where('decimal_purchase', $decimal_purchase)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $providers);
        $provider = $providers->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $provider = Provider::factory()->create();

        $response = $this->get(route('providers.show', $provider));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ProviderController::class,
            'update',
            \App\Http\Requests\Api\v1\ProviderUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $provider = Provider::factory()->create();
        $legal_name = fake()->word();
        $comercial_name = fake()->word();
        $document_type_id = fake()->numberBetween(-100000, 100000);
        $document_number = fake()->word();
        $economic_activity_id = fake()->numberBetween(-100000, 100000);
        $provider_type_id = fake()->numberBetween(-100000, 100000);
        $payment_type_id = fake()->numberBetween(-100000, 100000);
        $credit_days = fake()->numberBetween(-10000, 10000);
        $credit_limit = fake()->randomFloat(/** decimal_attributes **/);
        $debit_balance = fake()->randomFloat(/** decimal_attributes **/);
        $last_purchase = Carbon::parse(fake()->date());
        $decimal_purchase = fake()->numberBetween(-10000, 10000);
        $is_active = fake()->boolean();

        $response = $this->put(route('providers.update', $provider), [
            'legal_name' => $legal_name,
            'comercial_name' => $comercial_name,
            'document_type_id' => $document_type_id,
            'document_number' => $document_number,
            'economic_activity_id' => $economic_activity_id,
            'provider_type_id' => $provider_type_id,
            'payment_type_id' => $payment_type_id,
            'credit_days' => $credit_days,
            'credit_limit' => $credit_limit,
            'debit_balance' => $debit_balance,
            'last_purchase' => $last_purchase->toDateString(),
            'decimal_purchase' => $decimal_purchase,
            'is_active' => $is_active,
        ]);

        $provider->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($legal_name, $provider->legal_name);
        $this->assertEquals($comercial_name, $provider->comercial_name);
        $this->assertEquals($document_type_id, $provider->document_type_id);
        $this->assertEquals($document_number, $provider->document_number);
        $this->assertEquals($economic_activity_id, $provider->economic_activity_id);
        $this->assertEquals($provider_type_id, $provider->provider_type_id);
        $this->assertEquals($payment_type_id, $provider->payment_type_id);
        $this->assertEquals($credit_days, $provider->credit_days);
        $this->assertEquals($credit_limit, $provider->credit_limit);
        $this->assertEquals($debit_balance, $provider->debit_balance);
        $this->assertEquals($last_purchase, $provider->last_purchase);
        $this->assertEquals($decimal_purchase, $provider->decimal_purchase);
        $this->assertEquals($is_active, $provider->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $provider = Provider::factory()->create();

        $response = $this->delete(route('providers.destroy', $provider));

        $response->assertNoContent();

        $this->assertModelMissing($provider);
    }
}
