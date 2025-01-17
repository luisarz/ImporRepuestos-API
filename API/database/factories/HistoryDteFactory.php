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
            'version' => $this->faker->word(),
            'ambiente' => $this->faker->word(),
            'status' => $this->faker->randomElement(["1","2"]),
            'cod_geneneration' => $this->faker->word(),
            'receipt_stamp' => $this->faker->word(),
            'fhProcesamiento' => $this->faker->word(),
            'clasificaMsg' => $this->faker->word(),
            'codigoMsg' => $this->faker->word(),
            'descripcionMsg' => $this->faker->word(),
            'observaciones' => $this->faker->word(),
            'dte' => $this->faker->word(),
        ];
    }
}
