<?php

namespace App\Models;

use App\Enums\IngredientReviewDecision;
use App\Enums\IngredientReviewStatus;
use Database\Factories\IngredientReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property IngredientReviewStatus $status
 * @property IngredientReviewDecision|null $decision
 * @property Carbon|null $valid_until
 */
#[Fillable([
    'lab_ingredient_id',
    'submitted_by',
    'submitted_at',
    'reviewer_id',
    'status',
    'decision',
    'decided_at',
    'comment',
    'submission_snapshot',
    'submitted_document_ids',
    'submitted_fingerprint',
    'valid_until',
])]
class IngredientReview extends Model
{
    /** @use HasFactory<IngredientReviewFactory> */
    use HasFactory;

    /** @return BelongsTo<LabIngredient, $this> */
    public function labIngredient(): BelongsTo
    {
        return $this->belongsTo(LabIngredient::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'status' => IngredientReviewStatus::class,
            'decision' => IngredientReviewDecision::class,
            'decided_at' => 'datetime',
            'submission_snapshot' => 'array',
            'submitted_document_ids' => 'array',
            'valid_until' => 'date',
        ];
    }
}
