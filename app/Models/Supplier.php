<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['legal_name', 'country_code', 'website', 'contact_name', 'email', 'phone', 'is_active'])]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    protected $attributes = ['is_active' => true];

    /** @return BelongsTo<Country, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code');
    }

    /** @return HasMany<LabIngredient, $this> */
    public function labIngredients(): HasMany
    {
        return $this->hasMany(LabIngredient::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
