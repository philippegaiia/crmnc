<?php

namespace App\Models;

use App\Enums\IngredientReviewStatus;
use App\Enums\OrganicCertificationStatus;
use Database\Factories\LabIngredientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int $company_id
 * @property int $ingredient_ref_id
 * @property int $supplier_id
 * @property string $commercial_name
 * @property string|null $supplier_reference
 * @property string|null $grade
 * @property OrganicCertificationStatus $organic_certification_status
 * @property string|null $organic_content_percentage
 * @property IngredientRef $ingredientRef
 * @property Supplier $supplier
 */
#[Fillable(['company_id', 'ingredient_ref_id', 'supplier_id', 'commercial_name', 'supplier_reference', 'grade', 'organic_certification_status', 'organic_content_percentage', 'notes'])]
class LabIngredient extends Model
{
    /** @use HasFactory<LabIngredientFactory> */
    use HasFactory;

    protected $attributes = [
        'organic_certification_status' => OrganicCertificationStatus::Unknown->value,
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<IngredientRef, $this> */
    public function ingredientRef(): BelongsTo
    {
        return $this->belongsTo(IngredientRef::class);
    }

    /** @return BelongsTo<Supplier, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** @return HasMany<FormulaLine, $this> */
    public function formulaLines(): HasMany
    {
        return $this->hasMany(FormulaLine::class);
    }

    /** @return MorphMany<Document, $this> */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /** @return HasMany<IngredientReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(IngredientReview::class);
    }

    /** @return HasOne<IngredientReview, $this> */
    public function latestReview(): HasOne
    {
        return $this->hasOne(IngredientReview::class)->latestOfMany();
    }

    public function hasPendingReview(): bool
    {
        return $this->reviews()
            ->whereIn('status', [
                IngredientReviewStatus::Submitted,
                IngredientReviewStatus::UnderReview,
            ])
            ->exists();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'organic_certification_status' => OrganicCertificationStatus::class,
            'organic_content_percentage' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (LabIngredient $ingredient): void {
            if ($ingredient->exists
                && $ingredient->isDirty([
                    'ingredient_ref_id',
                    'supplier_id',
                    'commercial_name',
                    'supplier_reference',
                    'grade',
                    'organic_certification_status',
                    'organic_content_percentage',
                ])
                && $ingredient->hasPendingReview()) {
                throw ValidationException::withMessages([
                    'review' => 'Les informations soumises ne peuvent pas être modifiées pendant la revue.',
                ]);
            }

            if ($ingredient->organic_content_percentage === null) {
                return;
            }

            $percentage = (float) $ingredient->organic_content_percentage;

            if ($percentage < 0 || $percentage > 100) {
                throw ValidationException::withMessages([
                    'organic_content_percentage' => 'Le pourcentage bio doit être compris entre 0 et 100.',
                ]);
            }
        });
    }
}
