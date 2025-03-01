<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\EconomicActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\EconomicActivityController
 */
final class EconomicActivityControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $economicActivities = EconomicActivity::factory()->count(3)->create();

        $response = $this->get(route('economic-activities.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\EconomicActivityController::class,
            'store',
            \App\Http\Requests\Api\v1\EconomicActivityStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('economic-activities.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $economicActivities = EconomicActivity::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $economicActivities);
        $economicActivity = $economicActivities->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $economicActivity = EconomicActivity::factory()->create();

        $response = $this->get(route('economic-activities.show', $economicActivity));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\EconomicActivityController::class,
            'update',
            \App\Http\Requests\Api\v1\EconomicActivityUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $economicActivity = EconomicActivity::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('economic-activities.update', $economicActivity), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $economicActivity->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $economicActivity->code);
        $this->assertEquals($description, $economicActivity->description);
        $this->assertEquals($is_active, $economicActivity->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $economicActivity = EconomicActivity::factory()->create();

        $response = $this->delete(route('economic-activities.destroy', $economicActivity));

        $response->assertNoContent();

        $this->assertModelMissing($economicActivity);
    }
}
