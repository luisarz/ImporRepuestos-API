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
            'payment_method_id' => $this->faker->numberBetween(-100000, 100000),
            'casher_id' => Employee::factory(),
            'payment_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'actual_balance' => $this->faker->randomFloat(0, 0, 9999999999.),
            'bank_acount_id' => $this->faker->randomNumber(),
            'reference' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
