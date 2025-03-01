<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Batch;
use App\Models\BatchCodeOrigen;

class BatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Batch::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => fake()->word(),
            'origen_code' => BatchCodeOrigen::factory(),
            'inventory_id' => fake()->randomNumber(),
            'incoming_date' => fake()->date(),
            'expiration_date' => fake()->date(),
            'initial_quantity' => fake()->randomFloat(0, 0, 9999999999.),
            'available_quantity' => fake()->randomFloat(0, 0, 9999999999.),
            'observations' => fake()->word(),
            'is_active' => fake()->boolean(),
        ];
    }
}
