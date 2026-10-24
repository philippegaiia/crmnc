<?php

namespace Database\Factories;

use App\Enums\OrganicCertificationStatus;
use App\Models\Company;
use App\Models\IngredientRef;
use App\Models\LabIngredient;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabIngredient>
 */
class LabIngredientFactory extends Factory
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
            'ingredient_ref_id' => IngredientRef::factory(),
            'supplier_id' => Supplier::factory(),
            'commercial_name' => fake()->unique()->words(3, true),
            'supplier_reference' => fake()->unique()->bothify('MAT-####'),
            'grade' => fake()->randomElement(['Cosmétique', 'Biologique', 'Pharmacopée']),
            'organic_certification_status' => OrganicCertificationStatus::Unknown,
            'organic_content_percentage' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
