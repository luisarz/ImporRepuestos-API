<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CompanyController
 */
final class CompanyControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $companies = Company::factory()->count(3)->create();

        $response = $this->get(route('companies.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CompanyController::class,
            'store',
            \App\Http\Requests\Api\v1\CompanyStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $district_id = fake()->numberBetween(-100000, 100000);
        $economic_activity_id = fake()->numberBetween(-100000, 100000);
        $company_name = fake()->word();
        $nrc = fake()->word();
        $nit = fake()->word();
        $phone = fake()->phoneNumber();
        $whatsapp = fake()->word();
        $email = fake()->safeEmail();
        $address = fake()->word();
        $web = fake()->numberBetween(-100000, 100000);
        $api_key_mh = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->post(route('companies.store'), [
            'district_id' => $district_id,
            'economic_activity_id' => $economic_activity_id,
            'company_name' => $company_name,
            'nrc' => $nrc,
            'nit' => $nit,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'email' => $email,
            'address' => $address,
            'web' => $web,
            'api_key_mh' => $api_key_mh,
            'is_active' => $is_active,
        ]);

        $companies = Company::query()
            ->where('district_id', $district_id)
            ->where('economic_activity_id', $economic_activity_id)
            ->where('company_name', $company_name)
            ->where('nrc', $nrc)
            ->where('nit', $nit)
            ->where('phone', $phone)
            ->where('whatsapp', $whatsapp)
            ->where('email', $email)
            ->where('address', $address)
            ->where('web', $web)
            ->where('api_key_mh', $api_key_mh)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $companies);
        $company = $companies->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $company = Company::factory()->create();

        $response = $this->get(route('companies.show', $company));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CompanyController::class,
            'update',
            \App\Http\Requests\Api\v1\CompanyUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $company = Company::factory()->create();
        $district_id = fake()->numberBetween(-100000, 100000);
        $economic_activity_id = fake()->numberBetween(-100000, 100000);
        $company_name = fake()->word();
        $nrc = fake()->word();
        $nit = fake()->word();
        $phone = fake()->phoneNumber();
        $whatsapp = fake()->word();
        $email = fake()->safeEmail();
        $address = fake()->word();
        $web = fake()->numberBetween(-100000, 100000);
        $api_key_mh = fake()->word();
        $is_active = fake()->boolean();

        $response = $this->put(route('companies.update', $company), [
            'district_id' => $district_id,
            'economic_activity_id' => $economic_activity_id,
            'company_name' => $company_name,
            'nrc' => $nrc,
            'nit' => $nit,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'email' => $email,
            'address' => $address,
            'web' => $web,
            'api_key_mh' => $api_key_mh,
            'is_active' => $is_active,
        ]);

        $company->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($district_id, $company->district_id);
        $this->assertEquals($economic_activity_id, $company->economic_activity_id);
        $this->assertEquals($company_name, $company->company_name);
        $this->assertEquals($nrc, $company->nrc);
        $this->assertEquals($nit, $company->nit);
        $this->assertEquals($phone, $company->phone);
        $this->assertEquals($whatsapp, $company->whatsapp);
        $this->assertEquals($email, $company->email);
        $this->assertEquals($address, $company->address);
        $this->assertEquals($web, $company->web);
        $this->assertEquals($api_key_mh, $company->api_key_mh);
        $this->assertEquals($is_active, $company->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $company = Company::factory()->create();

        $response = $this->delete(route('companies.destroy', $company));

        $response->assertNoContent();

        $this->assertModelMissing($company);
    }
}
