<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\EmployeeController
 */
final class EmployeeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $employees = Employee::factory()->count(3)->create();

        $response = $this->get(route('employees.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\EmployeeController::class,
            'store',
            \App\Http\Requests\Api\v1\EmployeeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $warehouse_id = fake()->numberBetween(-100000, 100000);
        $job_title_id = fake()->numberBetween(-100000, 100000);
        $name = fake()->name();
        $last_name = fake()->lastName();
        $gender = fake()->randomElement(/** enum_attributes **/);
        $dui = fake()->word();
        $nit = fake()->word();
        $phone = fake()->phoneNumber();
        $email = fake()->safeEmail();
        $district_id = fake()->numberBetween(-100000, 100000);
        $address = fake()->word();
        $comision_porcentage = fake()->randomFloat(/** decimal_attributes **/);
        $is_active = fake()->boolean();
        $marital_status = fake()->randomElement(/** enum_attributes **/);
        $marital_name = fake()->word();
        $marital_phone = fake()->word();

        $response = $this->post(route('employees.store'), [
            'warehouse_id' => $warehouse_id,
            'job_title_id' => $job_title_id,
            'name' => $name,
            'last_name' => $last_name,
            'gender' => $gender,
            'dui' => $dui,
            'nit' => $nit,
            'phone' => $phone,
            'email' => $email,
            'district_id' => $district_id,
            'address' => $address,
            'comision_porcentage' => $comision_porcentage,
            'is_active' => $is_active,
            'marital_status' => $marital_status,
            'marital_name' => $marital_name,
            'marital_phone' => $marital_phone,
        ]);

        $employees = Employee::query()
            ->where('warehouse_id', $warehouse_id)
            ->where('job_title_id', $job_title_id)
            ->where('name', $name)
            ->where('last_name', $last_name)
            ->where('gender', $gender)
            ->where('dui', $dui)
            ->where('nit', $nit)
            ->where('phone', $phone)
            ->where('email', $email)
            ->where('district_id', $district_id)
            ->where('address', $address)
            ->where('comision_porcentage', $comision_porcentage)
            ->where('is_active', $is_active)
            ->where('marital_status', $marital_status)
            ->where('marital_name', $marital_name)
            ->where('marital_phone', $marital_phone)
            ->get();
        $this->assertCount(1, $employees);
        $employee = $employees->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->get(route('employees.show', $employee));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\EmployeeController::class,
            'update',
            \App\Http\Requests\Api\v1\EmployeeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $employee = Employee::factory()->create();
        $warehouse_id = fake()->numberBetween(-100000, 100000);
        $job_title_id = fake()->numberBetween(-100000, 100000);
        $name = fake()->name();
        $last_name = fake()->lastName();
        $gender = fake()->randomElement(/** enum_attributes **/);
        $dui = fake()->word();
        $nit = fake()->word();
        $phone = fake()->phoneNumber();
        $email = fake()->safeEmail();
        $district_id = fake()->numberBetween(-100000, 100000);
        $address = fake()->word();
        $comision_porcentage = fake()->randomFloat(/** decimal_attributes **/);
        $is_active = fake()->boolean();
        $marital_status = fake()->randomElement(/** enum_attributes **/);
        $marital_name = fake()->word();
        $marital_phone = fake()->word();

        $response = $this->put(route('employees.update', $employee), [
            'warehouse_id' => $warehouse_id,
            'job_title_id' => $job_title_id,
            'name' => $name,
            'last_name' => $last_name,
            'gender' => $gender,
            'dui' => $dui,
            'nit' => $nit,
            'phone' => $phone,
            'email' => $email,
            'district_id' => $district_id,
            'address' => $address,
            'comision_porcentage' => $comision_porcentage,
            'is_active' => $is_active,
            'marital_status' => $marital_status,
            'marital_name' => $marital_name,
            'marital_phone' => $marital_phone,
        ]);

        $employee->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($warehouse_id, $employee->warehouse_id);
        $this->assertEquals($job_title_id, $employee->job_title_id);
        $this->assertEquals($name, $employee->name);
        $this->assertEquals($last_name, $employee->last_name);
        $this->assertEquals($gender, $employee->gender);
        $this->assertEquals($dui, $employee->dui);
        $this->assertEquals($nit, $employee->nit);
        $this->assertEquals($phone, $employee->phone);
        $this->assertEquals($email, $employee->email);
        $this->assertEquals($district_id, $employee->district_id);
        $this->assertEquals($address, $employee->address);
        $this->assertEquals($comision_porcentage, $employee->comision_porcentage);
        $this->assertEquals($is_active, $employee->is_active);
        $this->assertEquals($marital_status, $employee->marital_status);
        $this->assertEquals($marital_name, $employee->marital_name);
        $this->assertEquals($marital_phone, $employee->marital_phone);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->delete(route('employees.destroy', $employee));

        $response->assertNoContent();

        $this->assertModelMissing($employee);
    }
}
