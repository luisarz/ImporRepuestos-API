<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\User;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'employee_id' => Employee::factory(),
            'email_verifed_at' => $this->faker->dateTime(),
            'password' => $this->faker->password(),
            'rememeber_tokend' => $this->faker->word(),
            'theme' => $this->faker->word(),
            'teheme_color' => $this->faker->word(),
        ];
    }
}