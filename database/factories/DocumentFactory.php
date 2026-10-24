<?php

namespace Database\Factories;

use App\Enums\DocumentAccessLevel;
use App\Enums\DocumentControlStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\LabIngredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'documentable_type' => LabIngredient::class,
            'documentable_id' => LabIngredient::factory(),
            'type' => DocumentType::TechnicalSheet,
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1_000, 2_000_000),
            'sha256' => fake()->sha256(),
            'version' => 1,
            'received_date' => today(),
            'control_status' => DocumentControlStatus::Received,
            'access_level' => DocumentAccessLevel::Adherent,
        ];
    }
}
