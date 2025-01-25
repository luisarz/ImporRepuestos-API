<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\District;
use App\Models\Employee;
use App\Models\JobsTitle;
use App\Models\Warehouse;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'job_title_id' => JobsTitle::factory(),
            'name' => fake()->name(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(["M","F"]),
            'dui' => fake()->word(),
            'nit' => fake()->word(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'photo' => '{}',
            'district_id' => District::factory(),
            'address' => fake()->word(),
            'comision_porcentage' => fake()->randomFloat(0, 0, 9999999999.),
            'is_active' => fake()->boolean(),
            'marital_status' => fake()->randomElement(["Soltero\/a","Casado\/a","Divorciado\/a","Viudo"]),
            'marital_name' => fake()->word(),
            'marital_phone' => fake()->word(),
        ];
    }
}
