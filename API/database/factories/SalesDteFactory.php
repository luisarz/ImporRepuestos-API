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
            'is_dte' => $this->faker->boolean(),
            'generation_code' => $this->faker->numberBetween(-100000, 100000),
            'billing_model' => $this->faker->numberBetween(-100000, 100000),
            'transmision_type' => $this->faker->numberBetween(-100000, 100000),
            'receipt_stamp' => $this->faker->numberBetween(-100000, 100000),
            'json_url' => $this->faker->numberBetween(-100000, 100000),
            'pdf_url' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
