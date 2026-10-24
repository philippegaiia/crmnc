<?php

namespace App\Services;

use App\Enums\DocumentControlStatus;
use App\Enums\DocumentType;
use App\Enums\IngredientReviewStatus;
use App\Enums\UserRole;
use App\Models\Document;
use App\Models\IngredientReview;
use App\Models\LabIngredient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IngredientReviewSubmissionService
{
    /**
     * Build the canonical submission snapshot, including display labels.
     *
     * @return array<string, mixed>
     */
    public function snapshotFor(LabIngredient $ingredient): array
    {
        $ingredient->loadMissing(['ingredientRef', 'supplier']);

        return [
            'ingredient_ref_id' => $ingredient->ingredient_ref_id,
            'ingredient_ref' => [
                'display_name' => $ingredient->ingredientRef->display_name,
                'normalized_inci' => $ingredient->ingredientRef->normalized_inci,
            ],
            'supplier_id' => $ingredient->supplier_id,
            'supplier' => [
                'legal_name' => $ingredient->supplier->legal_name,
            ],
            'commercial_name' => $ingredient->commercial_name,
            'supplier_reference' => $ingredient->supplier_reference,
            'grade' => $ingredient->grade,
            'organic_certification_status' => $ingredient->organic_certification_status->value,
            'organic_content_percentage' => $ingredient->organic_content_percentage,
            'components' => [],
        ];
    }

    /**
     * Return every document ID of the ingredient in ascending order.
     *
     * @return list<int>
     */
    public function documentIdsFor(LabIngredient $ingredient): array
    {
        $documents = $ingredient->relationLoaded('documents')
            ? $ingredient->documents
            : $ingredient->documents()->orderBy('id')->get();

        $documentIds = $documents
            ->map(fn (Document $document): int => (int) $document->getKey())
            ->sort()
            ->values()
            ->all();

        return array_values($documentIds);
    }

    /**
     * Compute the canonical approval fingerprint.
     *
     * Display labels are deliberately excluded: renaming a supplier or correcting an
     * INCI spelling must not invalidate approvals. Only essential fields and the
     * reviewed document IDs participate.
     *
     * @param  list<int>  $documentIds
     */
    public function fingerprintFor(LabIngredient $ingredient, array $documentIds): string
    {
        $essentialFields = [
            'ingredient_ref_id' => $ingredient->ingredient_ref_id,
            'supplier_id' => $ingredient->supplier_id,
            'commercial_name' => $ingredient->commercial_name,
            'supplier_reference' => $ingredient->supplier_reference,
            'grade' => $ingredient->grade,
            'organic_certification_status' => $ingredient->organic_certification_status->value,
            'organic_content_percentage' => $ingredient->organic_content_percentage,
            'components' => [],
        ];

        return hash('sha256', json_encode(
            [$essentialFields, $documentIds],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }

    public function submit(LabIngredient $ingredient, User $submitter): IngredientReview
    {
        return DB::transaction(function () use ($ingredient, $submitter): IngredientReview {
            $lockedIngredient = LabIngredient::query()
                ->with(['ingredientRef', 'supplier'])
                ->lockForUpdate()
                ->whereKey($ingredient->getKey())
                ->firstOrFail();

            $this->validateSubmitter($lockedIngredient, $submitter);

            if ($lockedIngredient->hasPendingReview()) {
                throw ValidationException::withMessages([
                    'review' => 'Cet ingrédient possède déjà une revue en attente.',
                ]);
            }

            $documents = $lockedIngredient->documents()
                ->orderBy('id')
                ->get();

            $lockedIngredient->setRelation('documents', $documents);

            $hasRequiredEvidence = $documents->contains(
                fn (Document $document): bool => in_array($document->type, [
                    DocumentType::TechnicalSheet,
                    DocumentType::Certificate,
                ], true),
            );

            if (! $hasRequiredEvidence) {
                throw ValidationException::withMessages([
                    'documents' => 'Ajoutez au moins une fiche technique ou un certificat avant la soumission.',
                ]);
            }

            $snapshot = $this->snapshotFor($lockedIngredient);
            $documentIds = $this->documentIdsFor($lockedIngredient);
            $fingerprint = $this->fingerprintFor($lockedIngredient, $documentIds);

            $review = $lockedIngredient->reviews()->create([
                'submitted_by' => $submitter->getKey(),
                'submitted_at' => now(),
                'status' => IngredientReviewStatus::Submitted,
                'submission_snapshot' => $snapshot,
                'submitted_document_ids' => $documentIds,
                'submitted_fingerprint' => $fingerprint,
            ]);

            $lockedIngredient->documents()->update([
                'control_status' => DocumentControlStatus::ToVerify,
            ]);

            return $review;
        });
    }

    private function validateSubmitter(LabIngredient $ingredient, User $submitter): void
    {
        $isAdherentForCompany = $submitter->role === UserRole::Adherent
            && $submitter->company_id === $ingredient->company_id;
        $isInternalManager = $submitter->role->isAssociationManager();

        if (! $submitter->is_active || (! $isAdherentForCompany && ! $isInternalManager)) {
            throw ValidationException::withMessages([
                'review' => "Vous n'êtes pas autorisé à soumettre cet ingrédient.",
            ]);
        }
    }
}
