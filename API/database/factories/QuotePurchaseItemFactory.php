<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\QuotePurchase;
use App\Models\QuotePurchaseItem;
use App\Models\Warehouse;

class QuotePurchaseItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = QuotePurchaseItem::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'qoute_purchase_id' => QuotePurchase::factory(),
            'inventory_id' => Warehouse::factory(),
            'quantity' => $this->faker->randomFloat(0, 0, 9999999999.),
            'price' => $this->faker->randomFloat(0, 0, 9999999999.),
            'discount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'total' => $this->faker->randomFloat(0, 0, 9999999999.),
            'is_compared' => $this->faker->numberBetween(-100000, 100000),
            'is_purchaseded' => $this->faker->boolean(),
            'description' => $this->faker->text(),
        ];
    }
}
