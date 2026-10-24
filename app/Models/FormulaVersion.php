<?php

namespace App\Models;

use App\Enums\FormulaVersionStatus;
use App\Enums\UserRole;
use Database\Factories\FormulaVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['formula_id', 'version_number', 'workflow_status', 'submitted_at', 'version_note', 'created_by'])]
class FormulaVersion extends Model
{
    /** @use HasFactory<FormulaVersionFactory> */
    use HasFactory;

    protected $attributes = ['workflow_status' => FormulaVersionStatus::Brouillon->value];

    /** @return BelongsTo<Formula, $this> */
    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<FormulaLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(FormulaLine::class)->orderBy('position');
    }

    /** @return HasMany<Product, $this> */
    public function currentProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'current_formula_version_id');
    }

    /**
     * @param  Builder<FormulaVersion>  $query
     * @return Builder<FormulaVersion>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->role === UserRole::Adherent
            ? $query->whereHas('formula', fn (Builder $formulaQuery): Builder => $formulaQuery->where('company_id', $user->company_id))
            : $query;
    }

    protected function casts(): array
    {
        return [
            'workflow_status' => FormulaVersionStatus::class,
            'submitted_at' => 'datetime',
        ];
    }
}
