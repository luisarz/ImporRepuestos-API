<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\SalesHeader;

class SalesHeaderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SalesHeader::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'cashbox_open_id' => $this->faker->randomNumber(),
            'sale_date' => $this->faker->dateTime(),
            'warehouse_id' => $this->faker->numberBetween(-100000, 100000),
            'document_type_id' => $this->faker->randomNumber(),
            'document_internal_number' => $this->faker->numberBetween(-100000, 100000),
            'seller_id' => Employee::factory(),
            'customer_id' => Customer::factory(),
            'operation_condition_id' => $this->faker->randomNumber(),
            'sale_status' => $this->faker->randomElement(["1","2","3"]),
            'have_retention' => $this->faker->boolean(),
            'net_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'taxe' => $this->faker->randomFloat(0, 0, 9999999999.),
            'discount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'retention' => $this->faker->randomFloat(0, 0, 9999999999.),
            'sale_total' => $this->faker->randomFloat(0, 0, 9999999999.),
            'payment_status' => $this->faker->numberBetween(-100000, 100000),
            'is_order' => $this->faker->boolean(),
            'is_order_closed_without_invoiced' => $this->faker->boolean(),
            'is_invoiced_order' => $this->faker->boolean(),
            'discount_percentage' => $this->faker->randomFloat(0, 0, 9999999999.),
            'discount_money' => $this->faker->randomFloat(0, 0, 9999999999.),
            'total_order_after_discount' => $this->faker->randomFloat(0, 0, 9999999999.),
        ];
    }
}
