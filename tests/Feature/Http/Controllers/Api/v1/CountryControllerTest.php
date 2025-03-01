<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\CountryController
 */
final class CountryControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $countries = Country::factory()->count(3)->create();

        $response = $this->get(route('countries.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CountryController::class,
            'store',
            \App\Http\Requests\Api\v1\CountryStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->randomLetter();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('countries.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $countries = Country::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $countries);
        $country = $countries->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $country = Country::factory()->create();

        $response = $this->get(route('countries.show', $country));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CountryController::class,
            'update',
            \App\Http\Requests\Api\v1\CountryUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $country = Country::factory()->create();
        $code = fake()->randomLetter();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('countries.update', $country), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $country->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $country->code);
        $this->assertEquals($description, $country->description);
        $this->assertEquals($is_active, $country->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $country = Country::factory()->create();

        $response = $this->delete(route('countries.destroy', $country));

        $response->assertNoContent();

        $this->assertModelMissing($country);
    }
}
