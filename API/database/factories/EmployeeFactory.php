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
            'name' => $this->faker->name(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(["M","F"]),
            'dui' => $this->faker->word(),
            'nit' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'photo' => '{}',
            'district_id' => District::factory(),
            'address' => $this->faker->word(),
            'comision_porcentage' => $this->faker->randomFloat(0, 0, 9999999999.),
            'is_active' => $this->faker->boolean(),
            'marital_status' => $this->faker->randomElement(["Soltero\/a","Casado\/a","Divorciado\/a","Viudo"]),
            'marital_name' => $this->faker->word(),
            'marital_phone' => $this->faker->word(),
        ];
    }
}
