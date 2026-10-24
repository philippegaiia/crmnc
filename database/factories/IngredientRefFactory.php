<?php

namespace Database\Factories;

use App\Enums\IngredientOrigin;
use App\Enums\IngredientRefStatus;
use App\Enums\LabelPosition;
use App\Enums\OrganicEligibility;
use App\Models\IngredientCategory;
use App\Models\IngredientRef;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IngredientRef>
 */
class IngredientRefFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ingredient_category_id' => IngredientCategory::factory(),
            'display_name' => fake()->unique()->words(2, true),
            'normalized_inci' => fake()->unique()->regexify('[A-Z]{5,12}'),
            'synonyms' => [fake()->word()],
            'origin' => fake()->randomElement(IngredientOrigin::cases()),
            'label_position' => LabelPosition::AStatuer,
            'conditions' => fake()->optional()->sentence(),
            'organic_eligibility' => OrganicEligibility::Inconnu,
            'status' => IngredientRefStatus::AValider,
        ];
    }
}
