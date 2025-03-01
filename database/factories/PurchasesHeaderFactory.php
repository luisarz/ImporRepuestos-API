<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Provider;
use App\Models\PurchasesHeader;
use App\Models\QuotePurchase;
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
            'quote_purchase_id' => QuotePurchase::factory(),
            'provider_id' => Provider::factory(),
            'purchcase_date' => fake()->date(),
            'serie' => fake()->word(),
            'purchase_number' => fake()->word(),
            'resolution' => fake()->word(),
            'purchase_type' => fake()->numberBetween(-100000, 100000),
            'paymen_method' => fake()->randomElement(["1","2"]),
            'payment_status' => fake()->randomElement(["1","2","3"]),
            'net_amount' => fake()->randomFloat(0, 0, 9999999999.),
            'tax_amount' => fake()->randomFloat(0, 0, 9999999999.),
            'retention_amount' => fake()->randomFloat(0, 0, 9999999999.),
            'total_purchase' => fake()->randomFloat(0, 0, 9999999999.),
            'employee_id' => fake()->randomNumber(),
            'status_purchase' => fake()->randomElement(["1","2","3"]),
        ];
    }
}
