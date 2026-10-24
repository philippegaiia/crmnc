<?php

namespace App\Models;

use App\Enums\IngredientOrigin;
use App\Enums\IngredientRefStatus;
use App\Enums\LabelPosition;
use App\Enums\OrganicEligibility;
use Database\Factories\IngredientRefFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property IngredientOrigin $origin
 * @property OrganicEligibility $organic_eligibility
 */
#[Fillable(['ingredient_category_id', 'display_name', 'normalized_inci', 'synonyms', 'origin', 'label_position', 'conditions', 'organic_eligibility', 'status'])]
class IngredientRef extends Model
{
    /** @use HasFactory<IngredientRefFactory> */
    use HasFactory;

    protected $attributes = [
        'label_position' => LabelPosition::AStatuer->value,
        'organic_eligibility' => OrganicEligibility::Inconnu->value,
        'status' => IngredientRefStatus::AValider->value,
    ];

    /** @return BelongsTo<IngredientCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'ingredient_category_id');
    }

    /** @return HasMany<FormulaLine, $this> */
    public function formulaLines(): HasMany
    {
        return $this->hasMany(FormulaLine::class);
    }

    /** @return HasMany<LabIngredient, $this> */
    public function labIngredients(): HasMany
    {
        return $this->hasMany(LabIngredient::class);
    }

    protected function casts(): array
    {
        return [
            'synonyms' => 'array',
            'origin' => IngredientOrigin::class,
            'label_position' => LabelPosition::class,
            'organic_eligibility' => OrganicEligibility::class,
            'status' => IngredientRefStatus::class,
        ];
    }
}
