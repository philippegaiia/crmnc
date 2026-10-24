<?php

namespace Database\Factories;

use App\Enums\FormulaVersionStatus;
use App\Models\Formula;
use App\Models\FormulaVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormulaVersion>
 */
class FormulaVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'formula_id' => Formula::factory(),
            'version_number' => 1,
            'workflow_status' => FormulaVersionStatus::Brouillon,
            'version_note' => fake()->optional()->sentence(),
        ];
    }
}
