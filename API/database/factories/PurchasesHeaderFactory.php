<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Provider;
use App\Models\PurchasesHeader;
use App\Models\Warehouse;

class PurchasesHeaderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PurchasesHeader::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'warehouse' => Warehouse::factory(),
            'provider_id' => Provider::factory(),
            'purchcase_date' => $this->faker->date(),
            'serie' => $this->faker->word(),
            'purchase_number' => $this->faker->word(),
            'resolution' => $this->faker->word(),
            'purchase_type' => $this->faker->numberBetween(-100000, 100000),
            'payment_method' => $this->faker->randomElement(["1","2"]),
            'payment_status' => $this->faker->randomElement(["1","2","3"]),
            'net_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'tax_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'retention_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'total_purchase' => $this->faker->randomFloat(0, 0, 9999999999.),
            'employee_id' => $this->faker->randomNumber(),
            'status_purchase' => $this->faker->randomElement(["1","2","3"]),
        ];
    }
}
