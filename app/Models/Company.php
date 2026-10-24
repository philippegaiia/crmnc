<?php

namespace App\Models;

use App\Enums\CompanyCommercialStatus;
use App\Enums\CompanyOrigin;
use App\Enums\Language;
use App\Enums\UserRole;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'legal_name',
    'usual_name',
    'commercial_status',
    'origin',
    'preferred_language',
    'address_line_1',
    'address_line_2',
    'postal_code',
    'city',
    'region',
    'country_code',
    'phone',
    'email',
    'website',
    'linkedin_url',
    'instagram_url',
    'notes_internal',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, SoftDeletes;

    /** @return BelongsTo<Country, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code');
    }

    /** @return HasMany<Contact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return BelongsToMany<ProductCategory, $this> */
    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class)->withTimestamps();
    }

    /** @return HasMany<Formula, $this> */
    public function formulas(): HasMany
    {
        return $this->hasMany(Formula::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<LabIngredient, $this> */
    public function labIngredients(): HasMany
    {
        return $this->hasMany(LabIngredient::class);
    }

    /**
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->role === UserRole::Adherent) {
            return $query->whereKey($user->company_id);
        }

        return $query;
    }

    protected function casts(): array
    {
        return [
            'commercial_status' => CompanyCommercialStatus::class,
            'origin' => CompanyOrigin::class,
            'preferred_language' => Language::class,
        ];
    }
}
