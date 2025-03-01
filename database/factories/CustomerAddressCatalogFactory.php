<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\CustomerAddressCatalog;
use App\Models\District;

class CustomerAddressCatalogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomerAddressCatalog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'address_reference' => fake()->word(),
            'is_active' => fake()->boolean(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'contact' => fake()->word(),
            'contact_phone' => fake()->word(),
            'contact_email' => fake()->word(),
        ];
    }
}
