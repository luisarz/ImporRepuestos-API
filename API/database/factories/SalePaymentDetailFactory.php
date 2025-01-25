<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\SalePaymentDetail;
use App\Models\SalesHeader;

class SalePaymentDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SalePaymentDetail::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sale_id' => SalesHeader::factory(),
            'payment_method_id' => fake()->numberBetween(-100000, 100000),
            'casher_id' => Employee::factory(),
            'payment_amount' => fake()->randomFloat(0, 0, 9999999999.),
            'actual_balance' => fake()->randomFloat(0, 0, 9999999999.),
            'bank_account_id' => fake()->randomNumber(),
            'reference' => fake()->word(),
            'is_active' => fake()->boolean(),
        ];
    }
}
