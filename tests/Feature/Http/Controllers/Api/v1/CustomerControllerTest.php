<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CustomerController
 */
final class CustomerControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $customers = Customer::factory()->count(3)->create();

        $response = $this->get(route('customers.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerController::class,
            'store',
            \App\Http\Requests\Api\v1\CustomerStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $customer_type = fake()->numberBetween(-100000, 100000);
        $internal_code = fake()->word();
        $document_type_id = fake()->numberBetween(-100000, 100000);
        $document_number = fake()->word();
        $name = fake()->name();
        $last_name = fake()->lastName();
        $warehouse = fake()->numberBetween(-100000, 100000);
        $nrc = fake()->word();
        $nit = fake()->word();
        $is_exempt = fake()->boolean();
        $sales_type = fake()->randomElement(/** enum_attributes **/);
        $is_creditable = fake()->boolean();
        $address = fake()->word();
        $credit_limit = fake()->randomFloat(/** decimal_attributes **/);
        $credit_amount = fake()->randomFloat(/** decimal_attributes **/);
        $is_delivery = fake()->boolean();

        $response = $this->post(route('customers.store'), [
            'customer_type' => $customer_type,
            'internal_code' => $internal_code,
            'document_type_id' => $document_type_id,
            'document_number' => $document_number,
            'name' => $name,
            'last_name' => $last_name,
            'warehouse' => $warehouse,
            'nrc' => $nrc,
            'nit' => $nit,
            'is_exempt' => $is_exempt,
            'sales_type' => $sales_type,
            'is_creditable' => $is_creditable,
            'address' => $address,
            'credit_limit' => $credit_limit,
            'credit_amount' => $credit_amount,
            'is_delivery' => $is_delivery,
        ]);

        $customers = Customer::query()
            ->where('customer_type', $customer_type)
            ->where('internal_code', $internal_code)
            ->where('document_type_id', $document_type_id)
            ->where('document_number', $document_number)
            ->where('name', $name)
            ->where('last_name', $last_name)
            ->where('warehouse', $warehouse)
            ->where('nrc', $nrc)
            ->where('nit', $nit)
            ->where('is_exempt', $is_exempt)
            ->where('sales_type', $sales_type)
            ->where('is_creditable', $is_creditable)
            ->where('address', $address)
            ->where('credit_limit', $credit_limit)
            ->where('credit_amount', $credit_amount)
            ->where('is_delivery', $is_delivery)
            ->get();
        $this->assertCount(1, $customers);
        $customer = $customers->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->get(route('customers.show', $customer));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CustomerController::class,
            'update',
            \App\Http\Requests\Api\v1\CustomerUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $customer = Customer::factory()->create();
        $customer_type = fake()->numberBetween(-100000, 100000);
        $internal_code = fake()->word();
        $document_type_id = fake()->numberBetween(-100000, 100000);
        $document_number = fake()->word();
        $name = fake()->name();
        $last_name = fake()->lastName();
        $warehouse = fake()->numberBetween(-100000, 100000);
        $nrc = fake()->word();
        $nit = fake()->word();
        $is_exempt = fake()->boolean();
        $sales_type = fake()->randomElement(/** enum_attributes **/);
        $is_creditable = fake()->boolean();
        $address = fake()->word();
        $credit_limit = fake()->randomFloat(/** decimal_attributes **/);
        $credit_amount = fake()->randomFloat(/** decimal_attributes **/);
        $is_delivery = fake()->boolean();

        $response = $this->put(route('customers.update', $customer), [
            'customer_type' => $customer_type,
            'internal_code' => $internal_code,
            'document_type_id' => $document_type_id,
            'document_number' => $document_number,
            'name' => $name,
            'last_name' => $last_name,
            'warehouse' => $warehouse,
            'nrc' => $nrc,
            'nit' => $nit,
            'is_exempt' => $is_exempt,
            'sales_type' => $sales_type,
            'is_creditable' => $is_creditable,
            'address' => $address,
            'credit_limit' => $credit_limit,
            'credit_amount' => $credit_amount,
            'is_delivery' => $is_delivery,
        ]);

        $customer->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($customer_type, $customer->customer_type);
        $this->assertEquals($internal_code, $customer->internal_code);
        $this->assertEquals($document_type_id, $customer->document_type_id);
        $this->assertEquals($document_number, $customer->document_number);
        $this->assertEquals($name, $customer->name);
        $this->assertEquals($last_name, $customer->last_name);
        $this->assertEquals($warehouse, $customer->warehouse);
        $this->assertEquals($nrc, $customer->nrc);
        $this->assertEquals($nit, $customer->nit);
        $this->assertEquals($is_exempt, $customer->is_exempt);
        $this->assertEquals($sales_type, $customer->sales_type);
        $this->assertEquals($is_creditable, $customer->is_creditable);
        $this->assertEquals($address, $customer->address);
        $this->assertEquals($credit_limit, $customer->credit_limit);
        $this->assertEquals($credit_amount, $customer->credit_amount);
        $this->assertEquals($is_delivery, $customer->is_delivery);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->delete(route('customers.destroy', $customer));

        $response->assertNoContent();

        $this->assertModelMissing($customer);
    }
}
