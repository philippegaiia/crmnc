<?php

namespace Database\Factories;

use App\Enums\IngredientReviewStatus;
use App\Models\IngredientReview;
use App\Models\LabIngredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IngredientReview>
 */
class IngredientReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lab_ingredient_id' => LabIngredient::factory(),
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
            'status' => IngredientReviewStatus::Submitted,
            'submission_snapshot' => [],
            'submitted_document_ids' => [],
            'submitted_fingerprint' => fake()->sha256(),
        ];
    }
}
