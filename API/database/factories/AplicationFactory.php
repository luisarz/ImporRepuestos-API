<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Aplication;
use App\Models\Product;
use App\Models\Vehicle;

class AplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Aplication::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'vehicle_id' => Vehicle::factory(),
            'is_active' => fake()->numberBetween(-100000, 100000),
        ];
    }
}
