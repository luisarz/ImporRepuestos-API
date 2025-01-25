<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Batch;
use App\Models\PurchaseItem;
use App\Models\PurchasesHeader;

class PurchaseItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PurchaseItem::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'purchase_id' => PurchasesHeader::factory(),
            'batch_id' => Batch::factory(),
            'is_purched' => fake()->boolean(),
            'quantity' => fake()->randomFloat(0, 0, 9999999999.),
            'price' => fake()->randomFloat(0, 0, 9999999999.),
            'discount' => fake()->randomFloat(0, 0, 9999999999.),
            'total' => fake()->randomFloat(0, 0, 9999999999.),
        ];
    }
}
