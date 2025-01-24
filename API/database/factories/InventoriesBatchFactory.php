<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Batch;
use App\Models\InventoriesBatch;
use App\Models\Inventory;

class InventoriesBatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InventoriesBatch::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_inventory' => Inventory::factory(),
            'id_batch' => Batch::factory(),
            'quantity' => $this->faker->randomFloat(0, 0, 9999999999.),
            'operation_date' => $this->faker->dateTime(),
        ];
    }
}
