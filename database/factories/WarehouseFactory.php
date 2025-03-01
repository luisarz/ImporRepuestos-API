<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\District;
use App\Models\EconomicActivity;
use App\Models\StablishmentType;
use App\Models\Warehouse;

class WarehouseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'company_id' => fake()->randomNumber(),
            'stablishment_type' => StablishmentType::factory(),
            'name' => fake()->name(),
            'nrc' => fake()->word(),
            'nit' => fake()->word(),
            'district_id' => District::factory(),
            'economic_activity_id' => EconomicActivity::factory(),
            'address' => fake()->word(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'product_prices' => fake()->numberBetween(-10000, 10000),
            'logo' => '{}',
        ];
    }
}
