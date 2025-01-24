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
            'company_name' => $this->faker->word(),
            'nrc' => $this->faker->word(),
            'nit' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'whatsapp' => $this->faker->word(),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->word(),
            'web' => $this->faker->numberBetween(-100000, 100000),
            'api_key_mh' => $this->faker->word(),
            'logo' => '{}',
        ];
    }
}
