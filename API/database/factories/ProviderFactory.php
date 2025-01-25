<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\DocumentsTypesProvider;
use App\Models\EconomicActivity;
use App\Models\Provider;
use App\Models\ProvidersType;

class ProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Provider::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'legal_name' => fake()->word(),
            'comercial_name' => fake()->word(),
            'document_type_id' => DocumentsTypesProvider::factory(),
            'document_number' => fake()->word(),
            'economic_activity_id' => EconomicActivity::factory(),
            'provider_type_id' => ProvidersType::factory(),
            'payment_type_id' => fake()->randomNumber(),
            'credit_days' => fake()->numberBetween(-10000, 10000),
            'credit_limit' => fake()->randomFloat(0, 0, 9999999999.),
            'debit_balance' => fake()->randomFloat(0, 0, 9999999999.),
            'last_purchase' => fake()->date(),
            'decimal_purchase' => fake()->numberBetween(-10000, 10000),
            'is_active' => fake()->boolean(),
        ];
    }
}
