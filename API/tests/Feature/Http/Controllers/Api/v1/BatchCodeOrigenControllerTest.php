<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\BatchCodeOrigen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\BatchCodeOrigenController
 */
final class BatchCodeOrigenControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $batchCodeOrigens = BatchCodeOrigen::factory()->count(3)->create();

        $response = $this->get(route('batch-code-origens.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\BatchCodeOrigenController::class,
            'store',
            \App\Http\Requests\Api\v1\BatchCodeOrigenStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->numberBetween(-100000, 100000);

        $response = $this->post(route('batch-code-origens.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $batchCodeOrigens = BatchCodeOrigen::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $batchCodeOrigens);
        $batchCodeOrigen = $batchCodeOrigens->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $batchCodeOrigen = BatchCodeOrigen::factory()->create();

        $response = $this->get(route('batch-code-origens.show', $batchCodeOrigen));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\BatchCodeOrigenController::class,
            'update',
            \App\Http\Requests\Api\v1\BatchCodeOrigenUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $batchCodeOrigen = BatchCodeOrigen::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->numberBetween(-100000, 100000);

        $response = $this->put(route('batch-code-origens.update', $batchCodeOrigen), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $batchCodeOrigen->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $batchCodeOrigen->code);
        $this->assertEquals($description, $batchCodeOrigen->description);
        $this->assertEquals($is_active, $batchCodeOrigen->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $batchCodeOrigen = BatchCodeOrigen::factory()->create();

        $response = $this->delete(route('batch-code-origens.destroy', $batchCodeOrigen));

        $response->assertNoContent();

        $this->assertModelMissing($batchCodeOrigen);
    }
}
