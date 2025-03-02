<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Aplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\AplicationController
 */
final class AplicationControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function Index_behaves_as_expected(): void
    {
        $aplications = Aplication::factory()->count(3)->create();

        $response = $this->get(route('aplications.Index'));
    }


    #[Test]
    public function Create_behaves_as_expected(): void
    {
        $aplication = Aplication::factory()->create();

        $response = $this->get(route('aplications.Create'));
    }


    #[Test]
    public function Store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\api\v1\AplicationController::class,
            'Store',
            \App\Http\Requests\api\v1\AplicationControllerStoreRequest::class
        );
    }

    #[Test]
    public function Store_saves(): void
    {
        $product_id = $this->faker->numberBetween(-100000, 100000);
        $vehicle_id = $this->faker->numberBetween(-100000, 100000);

        $response = $this->get(route('aplications.Store'), [
            'product_id' => $product_id,
            'vehicle_id' => $vehicle_id,
        ]);

        $aplications = Aplication::query()
            ->where('product_id', $product_id)
            ->where('vehicle_id', $vehicle_id)
            ->get();
        $this->assertCount(1, $aplications);
        $aplication = $aplications->first();
    }


    #[Test]
    public function Destroy_deletes(): void
    {
        $aplication = Aplication::factory()->create();

        $response = $this->get(route('aplications.Destroy'));

        $this->assertModelMissing($aplication);
    }
}
