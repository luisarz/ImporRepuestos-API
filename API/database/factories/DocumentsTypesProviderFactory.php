<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\DocumentsTypesProvider;

class DocumentsTypesProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DocumentsTypesProvider::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'description' => $this->faker->text(),
            'is_active' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
