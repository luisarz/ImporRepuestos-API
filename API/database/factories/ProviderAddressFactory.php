<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Provider;
use App\Models\ProviderAddress;
use App\Models\ProviderAddressCatalog;

class ProviderAddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProviderAddress::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'address_id' => ProviderAddressCatalog::factory(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
