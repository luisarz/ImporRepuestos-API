<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\FuelType;
use App\Models\PlateType;
use App\Models\Vehicle;
use App\Models\VehicleModel;

class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'model_id' => VehicleModel::factory(),
            'model_two' => fake()->word(),
            'year' => fake()->word(),
            'chassis' => fake()->word(),
            'vin' => fake()->word(),
            'motor' => fake()->word(),
            'displacement' => fake()->word(),
            'motor_type' => fake()->word(),
            'fuel_type' => FuelType::factory(),
            'vehicle_class' => fake()->word(),
            'income_date' => fake()->date(),
            'municipality_id' => fake()->numberBetween(-100000, 100000),
            'antique' => fake()->word(),
            'plate_type' => PlateType::factory(),
            'capacity' => fake()->randomFloat(0, 0, 9999999999.),
            'tonnage' => fake()->randomFloat(0, 0, 9999999999.),
            'is_active' => fake()->boolean(),
        ];
    }
}
