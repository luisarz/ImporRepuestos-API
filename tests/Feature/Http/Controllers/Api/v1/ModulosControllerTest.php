<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Modulo;
use App\Models\Modulos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\ModulosController
 */
final class ModulosControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $modulos = Modulos::factory()->count(3)->create();

        $response = $this->get(route('modulos.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ModulosController::class,
            'store',
            \App\Http\Requests\Api\v1\ModulosStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $response = $this->post(route('modulos.store'));

        $response->assertCreated();
        $response->assertJsonStructure([]);

        $this->assertDatabaseHas(modulos, [ /* ... */ ]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $modulo = Modulos::factory()->create();

        $response = $this->get(route('modulos.show', $modulo));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\ModulosController::class,
            'update',
            \App\Http\Requests\Api\v1\ModulosUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $modulo = Modulos::factory()->create();

        $response = $this->put(route('modulos.update', $modulo));

        $modulo->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $modulo = Modulos::factory()->create();
        $modulo = Modulo::factory()->create();

        $response = $this->delete(route('modulos.destroy', $modulo));

        $response->assertNoContent();

        $this->assertModelMissing($modulo);
    }
}
