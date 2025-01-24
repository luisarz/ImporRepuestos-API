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
            'code' => $this->faker->word(),
            'origen_code' => BatchCodeOrigen::factory(),
            'inventory_id' => $this->faker->randomNumber(),
            'incoming_date' => $this->faker->date(),
            'expiration_date' => $this->faker->date(),
            'initial_quantity' => $this->faker->randomFloat(0, 0, 9999999999.),
            'available_quantity' => $this->faker->randomFloat(0, 0, 9999999999.),
            'observations' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
