<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Inventory;
use App\Models\Price;

class PriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Price::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'inventory_id' => Inventory::factory(),
            'price_description' => fake()->word(),
            'price' => fake()->randomFloat(0, 0, 9999999999.),
            'max_discount' => fake()->randomFloat(0, 0, 9999999999.),
            'is_active' => fake()->boolean(),
            'quantity' => fake()->randomFloat(0, 0, 9999999999.),
        ];
    }
}
