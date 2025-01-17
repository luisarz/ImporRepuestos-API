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
            'company_id' => $this->faker->randomNumber(),
            'stablishment_type' => StablishmentType::factory(),
            'name' => $this->faker->name(),
            'nrc' => $this->faker->word(),
            'nit' => $this->faker->word(),
            'district_id' => District::factory(),
            'economic_activity_id' => EconomicActivity::factory(),
            'address' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'product_prices' => $this->faker->numberBetween(-10000, 10000),
            'logo' => '{}',
        ];
    }
}
