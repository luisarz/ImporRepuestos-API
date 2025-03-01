<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use App\Models\UnitMeasurement;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => fake()->word(),
            'original_code' => fake()->word(),
            'barcode' => fake()->word(),
            'description' => fake()->text(),
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(),
            'provider_id' => Provider::factory(),
            'unit_measurement_id' => UnitMeasurement::factory(),
            'description_measurement_id' => fake()->word(),
            'image' => '{}',
            'is_active' => fake()->boolean(),
            'is_taxed' => fake()->boolean(),
            'is_service' => fake()->boolean(),
        ];
    }
}
