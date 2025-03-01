<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\v1\VehicleController
 */
final class VehicleControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $vehicles = Vehicle::factory()->count(3)->create();

        $response = $this->get(route('vehicles.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\VehicleController::class,
            'store',
            \App\Http\Requests\Api\v1\VehicleStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $brand_id = fake()->numberBetween(-100000, 100000);
        $model_id = fake()->numberBetween(-100000, 100000);
        $model_two = fake()->word();
        $year = fake()->word();
        $chassis = fake()->word();
        $vin = fake()->word();
        $motor = fake()->word();
        $displacement = fake()->word();
        $motor_type = fake()->word();
        $fuel_type = fake()->numberBetween(-100000, 100000);
        $vehicle_class = fake()->word();
        $income_date = Carbon::parse(fake()->date());
        $municipality_id = fake()->numberBetween(-100000, 100000);
        $antique = fake()->word();
        $plate_type = fake()->numberBetween(-100000, 100000);
        $capacity = fake()->randomFloat(/** decimal_attributes **/);
        $tonnage = fake()->randomFloat(/** decimal_attributes **/);
        $is_active = fake()->boolean();

        $response = $this->post(route('vehicles.store'), [
            'brand_id' => $brand_id,
            'model_id' => $model_id,
            'model_two' => $model_two,
            'year' => $year,
            'chassis' => $chassis,
            'vin' => $vin,
            'motor' => $motor,
            'displacement' => $displacement,
            'motor_type' => $motor_type,
            'fuel_type' => $fuel_type,
            'vehicle_class' => $vehicle_class,
            'income_date' => $income_date->toDateString(),
            'municipality_id' => $municipality_id,
            'antique' => $antique,
            'plate_type' => $plate_type,
            'capacity' => $capacity,
            'tonnage' => $tonnage,
            'is_active' => $is_active,
        ]);

        $vehicles = Vehicle::query()
            ->where('brand_id', $brand_id)
            ->where('model_id', $model_id)
            ->where('model_two', $model_two)
            ->where('year', $year)
            ->where('chassis', $chassis)
            ->where('vin', $vin)
            ->where('motor', $motor)
            ->where('displacement', $displacement)
            ->where('motor_type', $motor_type)
            ->where('fuel_type', $fuel_type)
            ->where('vehicle_class', $vehicle_class)
            ->where('income_date', $income_date)
            ->where('municipality_id', $municipality_id)
            ->where('antique', $antique)
            ->where('plate_type', $plate_type)
            ->where('capacity', $capacity)
            ->where('tonnage', $tonnage)
            ->where('is_active', $is_active)
            ->get();
        $this->assertCount(1, $vehicles);
        $vehicle = $vehicles->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->get(route('vehicles.show', $vehicle));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\v1\VehicleController::class,
            'update',
            \App\Http\Requests\Api\v1\VehicleUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $vehicle = Vehicle::factory()->create();
        $brand_id = fake()->numberBetween(-100000, 100000);
        $model_id = fake()->numberBetween(-100000, 100000);
        $model_two = fake()->word();
        $year = fake()->word();
        $chassis = fake()->word();
        $vin = fake()->word();
        $motor = fake()->word();
        $displacement = fake()->word();
        $motor_type = fake()->word();
        $fuel_type = fake()->numberBetween(-100000, 100000);
        $vehicle_class = fake()->word();
        $income_date = Carbon::parse(fake()->date());
        $municipality_id = fake()->numberBetween(-100000, 100000);
        $antique = fake()->word();
        $plate_type = fake()->numberBetween(-100000, 100000);
        $capacity = fake()->randomFloat(/** decimal_attributes **/);
        $tonnage = fake()->randomFloat(/** decimal_attributes **/);
        $is_active = fake()->boolean();

        $response = $this->put(route('vehicles.update', $vehicle), [
            'brand_id' => $brand_id,
            'model_id' => $model_id,
            'model_two' => $model_two,
            'year' => $year,
            'chassis' => $chassis,
            'vin' => $vin,
            'motor' => $motor,
            'displacement' => $displacement,
            'motor_type' => $motor_type,
            'fuel_type' => $fuel_type,
            'vehicle_class' => $vehicle_class,
            'income_date' => $income_date->toDateString(),
            'municipality_id' => $municipality_id,
            'antique' => $antique,
            'plate_type' => $plate_type,
            'capacity' => $capacity,
            'tonnage' => $tonnage,
            'is_active' => $is_active,
        ]);

        $vehicle->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($brand_id, $vehicle->brand_id);
        $this->assertEquals($model_id, $vehicle->model_id);
        $this->assertEquals($model_two, $vehicle->model_two);
        $this->assertEquals($year, $vehicle->year);
        $this->assertEquals($chassis, $vehicle->chassis);
        $this->assertEquals($vin, $vehicle->vin);
        $this->assertEquals($motor, $vehicle->motor);
        $this->assertEquals($displacement, $vehicle->displacement);
        $this->assertEquals($motor_type, $vehicle->motor_type);
        $this->assertEquals($fuel_type, $vehicle->fuel_type);
        $this->assertEquals($vehicle_class, $vehicle->vehicle_class);
        $this->assertEquals($income_date, $vehicle->income_date);
        $this->assertEquals($municipality_id, $vehicle->municipality_id);
        $this->assertEquals($antique, $vehicle->antique);
        $this->assertEquals($plate_type, $vehicle->plate_type);
        $this->assertEquals($capacity, $vehicle->capacity);
        $this->assertEquals($tonnage, $vehicle->tonnage);
        $this->assertEquals($is_active, $vehicle->is_active);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->delete(route('vehicles.destroy', $vehicle));

        $response->assertNoContent();

        $this->assertModelMissing($vehicle);
    }
}
