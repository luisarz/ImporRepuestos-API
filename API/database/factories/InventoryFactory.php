<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;

class InventoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inventory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'cost_without_tax' => $this->faker->randomFloat(0, 0, 9999999999.),
            'cost_with_tax' => $this->faker->randomFloat(0, 0, 9999999999.),
            'stock_actual' => $this->faker->randomFloat(0, 0, 9999999999.),
            'stock_min' => $this->faker->randomFloat(0, 0, 9999999999.),
            'alert_stock_min' => $this->faker->boolean(),
            'stock_max' => $this->faker->randomFloat(0, 0, 9999999999.),
            'alert_stock_max' => $this->faker->boolean(),
            'max_discount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'last_purchase' => $this->faker->dateTime(),
        ];
    }
}
