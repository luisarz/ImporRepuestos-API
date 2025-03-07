<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\ModuleRol;
use App\Models\Module_Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\Module_RolController
 */
final class Module_RolControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $moduleRols = Module_Rol::factory()->count(3)->create();

        $response = $this->get(route('module_-rols.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\Module_RolController::class,
            'store',
            \App\Http\Requests\Api\v1\Module_RolStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $response = $this->post(route('module_-rols.store'));

        $response->assertCreated();
        $response->assertJsonStructure([]);

        $this->assertDatabaseHas(moduleRols, [ /* ... */ ]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $moduleRol = Module_Rol::factory()->create();

        $response = $this->get(route('module_-rols.show', $moduleRol));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\Module_RolController::class,
            'update',
            \App\Http\Requests\Api\v1\Module_RolUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $moduleRol = Module_Rol::factory()->create();

        $response = $this->put(route('module_-rols.update', $moduleRol));

        $moduleRol->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $moduleRol = Module_Rol::factory()->create();
        $moduleRol = ModuleRol::factory()->create();

        $response = $this->delete(route('module_-rols.destroy', $moduleRol));

        $response->assertNoContent();

        $this->assertModelMissing($moduleRol);
    }
}
