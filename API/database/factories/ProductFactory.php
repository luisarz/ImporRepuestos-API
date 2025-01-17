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
            'code' => $this->faker->word(),
            'original_code' => $this->faker->word(),
            'barcode' => $this->faker->word(),
            'description' => $this->faker->text(),
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(),
            'unit_measurement_id' => UnitMeasurement::factory(),
            'provider_id' => Provider::factory(),
            'description_measurement_id' => $this->faker->numberBetween(-100000, 100000),
            'is_service' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(),
            'is_taxed' => $this->faker->boolean(),
            'image' => '{}',
        ];
    }
}
