<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\HistoryDte;
use App\Models\SalesDte;

class HistoryDteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HistoryDte::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sale_dte_id' => SalesDte::factory(),
            'version' => fake()->word(),
            'ambiente' => fake()->word(),
            'status' => fake()->randomElement(["1","2"]),
            'code_generation' => fake()->word(),
            'receipt_stamp' => fake()->word(),
            'fhProcesamiento' => fake()->dateTime(),
            'clasifica_msg' => fake()->word(),
            'code_mgs' => fake()->word(),
            'description_msg' => fake()->word(),
            'observations' => fake()->word(),
            'dte' => fake()->word(),
        ];
    }
}
