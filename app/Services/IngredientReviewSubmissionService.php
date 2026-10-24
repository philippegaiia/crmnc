<?php

namespace App\Services;

use App\Enums\DocumentControlStatus;
use App\Enums\DocumentType;
use App\Enums\IngredientReviewStatus;
use App\Enums\UserRole;
use App\Models\IngredientReview;
use App\Models\LabIngredient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IngredientReviewSubmissionService
{
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

            $hasRequiredEvidence = $documents->contains(
                fn ($document): bool => in_array($document->type, [
                    DocumentType::TechnicalSheet,
                    DocumentType::Certificate,
                ], true),
            );

            if (! $hasRequiredEvidence) {
                throw ValidationException::withMessages([
                    'documents' => 'Ajoutez au moins une fiche technique ou un certificat avant la soumission.',
                ]);
            }

            $snapshot = [
                'ingredient_ref_id' => $lockedIngredient->ingredient_ref_id,
                'ingredient_ref' => [
                    'display_name' => $lockedIngredient->ingredientRef->display_name,
                    'normalized_inci' => $lockedIngredient->ingredientRef->normalized_inci,
                ],
                'supplier_id' => $lockedIngredient->supplier_id,
                'supplier' => [
                    'legal_name' => $lockedIngredient->supplier->legal_name,
                ],
                'commercial_name' => $lockedIngredient->commercial_name,
                'supplier_reference' => $lockedIngredient->supplier_reference,
                'grade' => $lockedIngredient->grade,
                'organic_certification_status' => $lockedIngredient->organic_certification_status->value,
                'organic_content_percentage' => $lockedIngredient->organic_content_percentage,
                'components' => [],
            ];
            $documentIds = $documents->modelKeys();
            $fingerprint = hash('sha256', json_encode(
                [$snapshot, $documentIds],
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ));

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
