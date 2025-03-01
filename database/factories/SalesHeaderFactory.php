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
            'cashbox_open_id' => fake()->randomNumber(),
            'sale_date' => fake()->dateTime(),
            'warehouse_id' => fake()->numberBetween(-100000, 100000),
            'document_type_id' => fake()->randomNumber(),
            'document_internal_number' => fake()->numberBetween(-100000, 100000),
            'seller_id' => Employee::factory(),
            'customer_id' => Customer::factory(),
            'operation_condition_id' => fake()->randomNumber(),
            'sale_status' => fake()->randomElement(["1","2","3"]),
            'net_amount' => fake()->randomFloat(0, 0, 9999999999.),
            'tax' => fake()->randomFloat(0, 0, 9999999999.),
            'discount' => fake()->randomFloat(0, 0, 9999999999.),
            'have_retention' => fake()->boolean(),
            'retention' => fake()->randomFloat(0, 0, 9999999999.),
            'sale_total' => fake()->randomFloat(0, 0, 9999999999.),
            'payment_status' => fake()->numberBetween(-100000, 100000),
            'is_order' => fake()->boolean(),
            'is_order_closed_without_invoiced' => fake()->boolean(),
            'is_invoiced_order' => fake()->boolean(),
            'discount_percentage' => fake()->randomFloat(0, 0, 9999999999.),
            'discount_money' => fake()->randomFloat(0, 0, 9999999999.),
            'total_order_after_discount' => fake()->randomFloat(0, 0, 9999999999.),
            'is_active' => fake()->boolean(),
        ];
    }
}
