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
            'payment_method' => fake()->randomNumber(),
            'provider' => fake()->randomNumber(),
            'date' => fake()->date(),
            'amount_purchase' => fake()->randomFloat(0, 0, 9999999999.),
            'is_active' => fake()->boolean(),
            'is_purchased' => fake()->boolean(),
            'is_compared' => fake()->boolean(),
            'buyer_id' => Employee::factory(),
        ];
    }
}
