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
            'model_two' => $this->faker->word(),
            'year' => $this->faker->word(),
            'chassis' => $this->faker->word(),
            'vin' => $this->faker->word(),
            'motor' => $this->faker->word(),
            'displacement' => $this->faker->word(),
            'motor_type' => $this->faker->word(),
            'fuel_type' => FuelType::factory(),
            'vehicle_class' => $this->faker->word(),
            'income_date' => $this->faker->date(),
            'municipality_id' => $this->faker->numberBetween(-100000, 100000),
            'antique' => $this->faker->word(),
            'plate_type' => PlateType::factory(),
            'capacity' => $this->faker->randomFloat(0, 0, 9999999999.),
            'tonnage' => $this->faker->randomFloat(0, 0, 9999999999.),
        ];
    }
}
