<?php

namespace App\Filament\Resources\LabIngredients\Pages;

use App\Filament\Resources\LabIngredients\LabIngredientResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLabIngredient extends ViewRecord
{
    protected static string $resource = LabIngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
