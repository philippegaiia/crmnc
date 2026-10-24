<?php

namespace App\Filament\Resources\IngredientCategories\Pages;

use App\Filament\Resources\IngredientCategories\IngredientCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIngredientCategory extends ViewRecord
{
    protected static string $resource = IngredientCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
