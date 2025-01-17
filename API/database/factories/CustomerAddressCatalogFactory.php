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
            'address_reference' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'contact' => $this->faker->word(),
            'contact_phone' => $this->faker->numberBetween(-100000, 100000),
            'contact_email' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
