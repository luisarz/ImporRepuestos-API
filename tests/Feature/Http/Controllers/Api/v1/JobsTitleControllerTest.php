<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\JobsTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\JobsTitleController
 */
final class JobsTitleControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $jobsTitles = JobsTitle::factory()->count(3)->create();

        $response = $this->get(route('jobs-titles.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\JobsTitleController::class,
            'store',
            \App\Http\Requests\Api\v1\JobsTitleStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->post(route('jobs-titles.store'), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $jobsTitles = JobsTitle::query()
            ->where('code', $code)
            ->where('description', $description)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $jobsTitles);
        $jobsTitle = $jobsTitles->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $jobsTitle = JobsTitle::factory()->create();

        $response = $this->get(route('jobs-titles.show', $jobsTitle));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\JobsTitleController::class,
            'update',
            \App\Http\Requests\Api\v1\JobsTitleUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $jobsTitle = JobsTitle::factory()->create();
        $code = fake()->word();
        $description = fake()->text();
        $is_active = fake()->boolean();

        $response = $this->put(route('jobs-titles.update', $jobsTitle), [
            'code' => $code,
            'description' => $description,
            'is_active' => $is_active,
        ]);

        $jobsTitle->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $jobsTitle->code);
        $this->assertEquals($description, $jobsTitle->description);
        $this->assertEquals($is_active, $jobsTitle->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $jobsTitle = JobsTitle::factory()->create();

        $response = $this->delete(route('jobs-titles.destroy', $jobsTitle));

        $response->assertNoContent();

        $this->assertModelMissing($jobsTitle);
    }
}
