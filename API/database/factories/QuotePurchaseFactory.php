<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\QuotePurchase;

class QuotePurchaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = QuotePurchase::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'payment_method' => $this->faker->randomNumber(),
            'provider' => $this->faker->randomNumber(),
            'date' => $this->faker->date(),
            'amount_purchase' => $this->faker->randomFloat(0, 0, 9999999999.),
            'is_active' => $this->faker->boolean(),
            'is_purchaded' => $this->faker->boolean(),
            'is_compared' => $this->faker->boolean(),
            'buyer_id' => Employee::factory(),
        ];
    }
}
