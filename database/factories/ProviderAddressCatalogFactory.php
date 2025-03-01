<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\ProviderAddressCatalog;

class ProviderAddressCatalogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProviderAddressCatalog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'district_id' => fake()->randomNumber(),
            'address_reference' => fake()->word(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'seller' => fake()->word(),
            'seller_phone' => fake()->word(),
            'seller_email' => fake()->word(),
            'is_active' => fake()->boolean(),
        ];
    }
}
