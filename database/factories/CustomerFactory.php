<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\CustomerDocumentsType;
use App\Models\CustomerType;
use App\Models\Warehouse;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_type' => fake()->randomNumber(),
            'internal_code' => CustomerType::factory(),
            'document_type_id' => CustomerDocumentsType::factory(),
            'document_number' => fake()->word(),
            'name' => fake()->name(),
            'last_name' => fake()->lastName(),
            'warehouse' => Warehouse::factory(),
            'nrc' => fake()->word(),
            'nit' => fake()->word(),
            'is_exempt' => fake()->boolean(),
            'sales_type' => fake()->randomElement(["1","2","3","4"]),
            'is_creditable' => fake()->boolean(),
            'address' => fake()->word(),
            'credit_limit' => fake()->randomFloat(0, 0, 9999999999.),
            'credit_amount' => fake()->randomFloat(0, 0, 9999999999.),
            'is_delivery' => fake()->boolean(),
        ];
    }
}
