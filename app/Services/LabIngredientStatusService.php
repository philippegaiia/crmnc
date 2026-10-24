<?php

namespace App\Services;

use App\Enums\IngredientReviewDecision;
use App\Enums\IngredientReviewStatus;
use App\Enums\LabIngredientStatus;
use App\Models\IngredientReview;
use App\Models\LabIngredient;

class LabIngredientStatusService
{
    public function __construct(
        private readonly IngredientReviewSubmissionService $submissionService,
    ) {}

    public function status(LabIngredient $ingredient): LabIngredientStatus
    {
        $review = $ingredient->latestReview;

        if (! $review instanceof IngredientReview) {
            return LabIngredientStatus::NotSubmitted;
        }

        if ($review->status === IngredientReviewStatus::Submitted
            || $review->status === IngredientReviewStatus::UnderReview) {
            return LabIngredientStatus::AwaitingReview;
        }

        if ($review->status === IngredientReviewStatus::InformationRequested) {
            return LabIngredientStatus::InformationRequested;
        }

        if ($review->decision === IngredientReviewDecision::Refused) {
            return LabIngredientStatus::Refused;
        }

        if ($review->decision !== IngredientReviewDecision::Approved) {
            return LabIngredientStatus::NotSubmitted;
        }

        $currentFingerprint = $this->submissionService->fingerprintFor(
            $ingredient,
            $this->submissionService->documentIdsFor($ingredient),
        );

        if ($currentFingerprint !== $review->submitted_fingerprint) {
            return LabIngredientStatus::ChangedSinceApproval;
        }

        if ($review->valid_until !== null && $review->valid_until->lessThan(today())) {
            return LabIngredientStatus::ApprovalExpired;
        }

        return LabIngredientStatus::Approved;
    }

    public function hasValidApproval(LabIngredient $ingredient): bool
    {
        return $this->status($ingredient) === LabIngredientStatus::Approved;
    }
}
