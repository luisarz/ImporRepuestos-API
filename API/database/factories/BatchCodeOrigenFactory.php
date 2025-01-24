<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\BatchCodeOrigen;

class BatchCodeOrigenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BatchCodeOrigen::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'descripcion' => $this->faker->word(),
            'is_active' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
