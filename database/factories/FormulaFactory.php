<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Formula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Formula>
 */
class FormulaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'internal_code' => fake()->unique()->bothify('FOR-####'),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
