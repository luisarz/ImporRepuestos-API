<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\CustomerDocumentsType;
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
            'internal_code' => $this->faker->word(),
            'document_type_id' => CustomerDocumentsType::factory(),
            'document_number' => $this->faker->word(),
            'name' => $this->faker->name(),
            'last_name' => $this->faker->lastName(),
            'warehouse' => Warehouse::factory(),
            'nrc' => $this->faker->word(),
            'nit' => $this->faker->word(),
            'is_taxed' => $this->faker->boolean(),
            'sales_type' => $this->faker->randomElement(["1","2","3","4"]),
            'is_creditable' => $this->faker->boolean(),
            'address' => $this->faker->word(),
            'credit_limit' => $this->faker->numberBetween(-100000, 100000),
            'credit_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'is_delivery' => $this->faker->boolean(),
        ];
    }
}
