<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Interchange;
use App\Models\Interchanges;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\InterchangesController
 */
final class InterchangesControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $interchanges = Interchanges::factory()->count(3)->create();

        $response = $this->get(route('interchanges.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\InterchangesController::class,
            'store',
            \App\Http\Requests\Api\v1\InterchangesStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $response = $this->post(route('interchanges.store'));

        $response->assertCreated();
        $response->assertJsonStructure([]);

        $this->assertDatabaseHas(interchanges, [ /* ... */ ]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $interchange = Interchanges::factory()->create();

        $response = $this->get(route('interchanges.show', $interchange));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\InterchangesController::class,
            'update',
            \App\Http\Requests\Api\v1\InterchangesUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $interchange = Interchanges::factory()->create();

        $response = $this->put(route('interchanges.update', $interchange));

        $interchange->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $interchange = Interchanges::factory()->create();
        $interchange = Interchange::factory()->create();

        $response = $this->delete(route('interchanges.destroy', $interchange));

        $response->assertNoContent();

        $this->assertModelMissing($interchange);
    }
}
