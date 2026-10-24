<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\FormulaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'internal_code', 'description', 'is_active'])]
class Formula extends Model
{
    /** @use HasFactory<FormulaFactory> */
    use HasFactory;

    protected $attributes = ['is_active' => true];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<FormulaVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(FormulaVersion::class);
    }

    /**
     * @param  Builder<Formula>  $query
     * @return Builder<Formula>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->role === UserRole::Adherent
            ? $query->where('company_id', $user->company_id)
            : $query;
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
