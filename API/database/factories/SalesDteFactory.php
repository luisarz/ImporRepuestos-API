<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\SalesDte;
use App\Models\SalesHeader;

class SalesDteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SalesDte::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sale_id' => SalesHeader::factory(),
            'is_dte' => fake()->boolean(),
            'generation_code' => fake()->numberBetween(-100000, 100000),
            'billing_model' => fake()->randomNumber(),
            'transmition_type' => fake()->numberBetween(-100000, 100000),
            'receipt_stamp' => fake()->word(),
            'json_url' => fake()->word(),
            'pdf_url' => fake()->word(),
        ];
    }
}
