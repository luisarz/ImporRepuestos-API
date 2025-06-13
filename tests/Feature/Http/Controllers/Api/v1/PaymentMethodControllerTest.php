<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\PaymentMethodController
 */
final class PaymentMethodControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $paymentMethods = PaymentMethod::factory()->count(3)->create();

        $response = $this->get(route('payment-methods.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PaymentMethodController::class,
            'store',
            \App\Http\Requests\Api\v1\PaymentMethodStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $name = fake()->name();
        $is_active = fake()->boolean();

        $response = $this->post(route('payment-methods.store'), [
            'code' => $code,
            'name' => $name,
            'is_active' => $is_active,
        ]);

        $paymentMethods = PaymentMethod::query()
            ->where('code', $code)
            ->where('name', $name)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $paymentMethods);
        $paymentMethod = $paymentMethods->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->get(route('payment-methods.show', $paymentMethod));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\PaymentMethodController::class,
            'update',
            \App\Http\Requests\Api\v1\PaymentMethodUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $paymentMethod = PaymentMethod::factory()->create();
        $code = fake()->word();
        $name = fake()->name();
        $is_active = fake()->boolean();

        $response = $this->put(route('payment-methods.update', $paymentMethod), [
            'code' => $code,
            'name' => $name,
            'is_active' => $is_active,
        ]);

        $paymentMethod->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $paymentMethod->code);
        $this->assertEquals($name, $paymentMethod->name);
        $this->assertEquals($is_active, $paymentMethod->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->delete(route('payment-methods.destroy', $paymentMethod));

        $response->assertNoContent();

        $this->assertModelMissing($paymentMethod);
    }
}
