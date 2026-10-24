<?php

namespace Database\Factories;

use App\Models\FormulaLine;
use App\Models\FormulaVersion;
use App\Models\IngredientRef;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormulaLine>
 */
class FormulaLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'formula_version_id' => FormulaVersion::factory(),
            'ingredient_ref_id' => IngredientRef::factory(),
            'lab_ingredient_id' => null,
            'percentage' => fake()->randomFloat(4, 0.01, 100),
            'is_qsp' => false,
            'position' => fake()->numberBetween(1, 20),
            'technical_comment' => fake()->optional()->sentence(),
        ];
    }
}
