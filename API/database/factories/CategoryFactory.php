<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => fake()->word(),
            'description' => fake()->text(),
            'commission_percentage' => fake()->randomFloat(0, 0, 9999999999.),
            'category_parent_id' => Category::factory(),
            'is_active' => fake()->boolean(),
        ];
    }
}
