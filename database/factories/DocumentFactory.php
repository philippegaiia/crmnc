<?php

namespace Database\Factories;

use App\Enums\DocumentAccessLevel;
use App\Enums\DocumentControlStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\LabIngredient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
            'version' => 1,
            'received_date' => today(),
            'control_status' => DocumentControlStatus::Received,
            'access_level' => DocumentAccessLevel::Adherent,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Document $document): void {
            Storage::disk('local')->put(
                (string) $document->file_path,
                "%PDF-1.4\n%crmnc factory\n%%EOF\n",
            );
        });
    }
}
