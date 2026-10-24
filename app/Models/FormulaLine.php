<?php

namespace App\Models;

use Database\Factories\FormulaLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

#[Fillable(['formula_version_id', 'ingredient_ref_id', 'lab_ingredient_id', 'percentage', 'is_qsp', 'position', 'technical_comment'])]
class FormulaLine extends Model
{
    /** @use HasFactory<FormulaLineFactory> */
    use HasFactory;

    protected $attributes = ['is_qsp' => false];

    /** @return BelongsTo<FormulaVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(FormulaVersion::class, 'formula_version_id');
    }

    /** @return BelongsTo<IngredientRef, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(IngredientRef::class, 'ingredient_ref_id');
    }

    /** @return BelongsTo<LabIngredient, $this> */
    public function labIngredient(): BelongsTo
    {
        return $this->belongsTo(LabIngredient::class);
    }

    protected function casts(): array
    {
        return ['percentage' => 'decimal:4', 'is_qsp' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (FormulaLine $line): void {
            if ($line->lab_ingredient_id === null) {
                return;
            }

            $isCompatible = LabIngredient::query()
                ->whereKey($line->lab_ingredient_id)
                ->where('ingredient_ref_id', $line->ingredient_ref_id)
                ->whereHas('company.formulas.versions', fn ($query) => $query->whereKey($line->formula_version_id))
                ->exists();

            if (! $isCompatible) {
                throw ValidationException::withMessages([
                    'lab_ingredient_id' => "L'ingrédient laboratoire doit appartenir à la société de la formule et correspondre à l'ingrédient générique.",
                ]);
            }
        });
    }
}
