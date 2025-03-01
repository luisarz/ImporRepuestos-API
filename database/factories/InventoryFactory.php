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
            'last_cost_without_tax' => fake()->randomFloat(0, 0, 9999999999.),
            'last_cost_with_tax' => fake()->randomFloat(0, 0, 9999999999.),
            'stock_actual_quantity' => fake()->randomFloat(0, 0, 9999999999.),
            'stock_min' => fake()->randomFloat(0, 0, 9999999999.),
            'alert_stock_min' => fake()->boolean(),
            'stock_max' => fake()->randomFloat(0, 0, 9999999999.),
            'alert_stock_max' => fake()->boolean(),
            'last_purchase' => fake()->dateTime(),
            'is_service' => fake()->boolean(),
        ];
    }
}
