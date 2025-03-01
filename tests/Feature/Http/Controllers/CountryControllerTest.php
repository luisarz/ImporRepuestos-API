<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CountryController
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
            \App\Http\Controllers\CountryController::class,
            'store',
            \App\Http\Requests\CountryControllerStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $codigo = fake()->randomLetter();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('countries.store'), [
            'codigo' => $codigo,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $countries = Country::query()
            ->where('codigo', $codigo)
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
            \App\Http\Controllers\CountryController::class,
            'update',
            \App\Http\Requests\CountryControllerUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $country = Country::factory()->create();
        $codigo = fake()->randomLetter();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('countries.update', $country), [
            'codigo' => $codigo,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $country->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($codigo, $country->codigo);
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


    #[Test]
    public function Index_behaves_as_expected(): void
    {
        $countries = Country::factory()->count(3)->create();

        $response = $this->get(route('countries.Index'));
    }
}
