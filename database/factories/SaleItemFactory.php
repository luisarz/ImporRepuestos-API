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
            'inventory_id' => fake()->numberBetween(-100000, 100000),
            'batch_id' => InventoriesBatch::factory(),
            'saled' => fake()->boolean(),
            'quantity' => fake()->randomFloat(0, 0, 9999999999.),
            'price' => fake()->randomFloat(0, 0, 9999999999.),
            'discount' => fake()->randomFloat(0, 0, 9999999999.),
            'total' => fake()->randomFloat(0, 0, 9999999999.),
            'is_saled' => fake()->boolean(),
            'is_active' => fake()->boolean(),
        ];
    }
}
