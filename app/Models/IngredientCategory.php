<?php

namespace App\Models;

use Database\Factories\IngredientCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'is_active'])]
class IngredientCategory extends Model
{
    /** @use HasFactory<IngredientCategoryFactory> */
    use HasFactory;

    protected $attributes = ['is_active' => true];

    /** @return HasMany<IngredientRef, $this> */
    public function ingredients(): HasMany
    {
        return $this->hasMany(IngredientRef::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
