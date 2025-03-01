<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Company;
use App\Models\District;
use App\Models\EconomicActivity;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'economic_activity_id' => EconomicActivity::factory(),
            'company_name' => fake()->word(),
            'nrc' => fake()->word(),
            'nit' => fake()->word(),
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->word(),
            'email' => fake()->safeEmail(),
            'address' => fake()->word(),
            'web' => fake()->numberBetween(-100000, 100000),
            'api_key_mh' => fake()->word(),
            'logo' => '{}',
            'is_active' => fake()->boolean(),
        ];
    }
}
