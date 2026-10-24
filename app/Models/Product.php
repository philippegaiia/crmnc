<?php

namespace App\Models;

use App\Enums\ProductCommercialStatus;
use App\Enums\UserRole;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

#[Fillable(['company_id', 'product_category_id', 'name', 'brand', 'white_label_client', 'current_formula_version_id', 'commercial_status', 'notes'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $attributes = ['commercial_status' => ProductCommercialStatus::Brouillon->value];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<ProductCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /** @return BelongsTo<FormulaVersion, $this> */
    public function currentFormulaVersion(): BelongsTo
    {
        return $this->belongsTo(FormulaVersion::class, 'current_formula_version_id');
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->role === UserRole::Adherent
            ? $query->where('company_id', $user->company_id)
            : $query;
    }

    protected function casts(): array
    {
        return ['commercial_status' => ProductCommercialStatus::class];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->current_formula_version_id === null) {
                return;
            }

            $belongsToCompany = FormulaVersion::query()
                ->whereKey($product->current_formula_version_id)
                ->whereHas('formula', fn (Builder $query): Builder => $query->where('company_id', $product->company_id))
                ->exists();

            if (! $belongsToCompany) {
                throw ValidationException::withMessages([
                    'current_formula_version_id' => 'La version de formule doit appartenir à la même société que le produit.',
                ]);
            }
        });
    }
}
