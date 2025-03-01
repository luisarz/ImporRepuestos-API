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
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'employee_id' => Employee::factory(),
            'email_verifed_at' => fake()->dateTime(),
            'password' => fake()->password(),
            'rememeber_tokend' => fake()->word(),
            'theme' => fake()->word(),
            'teheme_color' => fake()->word(),
        ];
    }
}
