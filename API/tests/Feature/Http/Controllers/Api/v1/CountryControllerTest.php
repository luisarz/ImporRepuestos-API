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
    public function index_responds_with(): void
    {
        $countries = Country::factory()->count(3)->create();

        $response = $this->get(route('countries.index'));

        $response->assertNoContent();
    }


    #[Test]
    public function create_displays_view(): void
    {
        $country = Country::factory()->create();

        $response = $this->get(route('countries.create'));

        $response->assertOk();
        $response->assertViewIs('country.create');
        $response->assertViewHas('user');
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\CountryController::class,
            'store',
            \App\Http\Requests\Api\v1\CountryControllerStoreRequest::class
        );
    }

    #[Test]
    public function store_saves_and_redirects(): void
    {
        $codigo = $this->faker->randomLetter();
        $descripcion = $this->faker->word();
        $is_active = $this->faker->boolean();

        $response = $this->post(route('countries.store'), [
            'codigo' => $codigo,
            'descripcion' => $descripcion,
            'is_active' => $is_active,
        ]);

        $countries = Country::query()
            ->where('codigo', $codigo)
            ->where('descripcion', $descripcion)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $countries);
        $country = $countries->first();

        $response->assertRedirect(route('country.show', ['country' => $country]));
    }


    #[Test]
    public function show_displays_view(): void
    {
        $country = Country::factory()->create();
        $countries = Country::factory()->count(3)->create();

        $response = $this->get(route('countries.show', $country));

        $response->assertOk();
        $response->assertViewIs('country.show');
        $response->assertViewHas('country');
        $response->assertViewHas('comments');
    }
}
