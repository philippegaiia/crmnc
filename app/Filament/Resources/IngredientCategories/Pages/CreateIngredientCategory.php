<?php

namespace App\Filament\Resources\IngredientCategories\Pages;

use App\Filament\Resources\IngredientCategories\IngredientCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIngredientCategory extends CreateRecord
{
    protected static string $resource = IngredientCategoryResource::class;
}
