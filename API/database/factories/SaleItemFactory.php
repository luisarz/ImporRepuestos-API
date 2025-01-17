<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\InventoriesBatch;
use App\Models\SaleItem;
use App\Models\SalesHeader;

class SaleItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SaleItem::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sale_id' => SalesHeader::factory(),
            'inventory_id' => $this->faker->numberBetween(-100000, 100000),
            'batch_id' => InventoriesBatch::factory(),
            'saled' => $this->faker->numberBetween(-100000, 100000),
            'quantity' => $this->faker->randomFloat(0, 0, 9999999999.),
            'price' => $this->faker->numberBetween(-100000, 100000),
            'discount' => $this->faker->numberBetween(-100000, 100000),
            'total' => $this->faker->randomFloat(0, 0, 9999999999.),
        ];
    }
}
