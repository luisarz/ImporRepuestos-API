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
            'legal_name' => $this->faker->word(),
            'comercial_name' => $this->faker->word(),
            'document_type_id' => DocumentsTypesProvider::factory(),
            'document_number' => $this->faker->word(),
            'economic_activity_id' => EconomicActivity::factory(),
            'provider_type_id' => ProvidersType::factory(),
            'payment_type_id' => $this->faker->randomNumber(),
            'credit_days' => $this->faker->numberBetween(-10000, 10000),
            'credit_limit' => $this->faker->randomFloat(0, 0, 9999999999.),
            'debit_balance' => $this->faker->randomFloat(0, 0, 9999999999.),
            'last_purchase' => $this->faker->date(),
            'decimal_purchase' => $this->faker->numberBetween(-10000, 10000),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
