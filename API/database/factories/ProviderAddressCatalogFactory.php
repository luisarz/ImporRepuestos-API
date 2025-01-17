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
            'district_id' => $this->faker->randomNumber(),
            'address_reference' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'seller' => $this->faker->word(),
            'seller_phone' => $this->faker->word(),
            'seller_email' => $this->faker->word(),
        ];
    }
}
