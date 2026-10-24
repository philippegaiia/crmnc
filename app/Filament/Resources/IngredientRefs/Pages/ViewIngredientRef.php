<?php

namespace App\Filament\Resources\IngredientRefs\Pages;

use App\Filament\Resources\IngredientRefs\IngredientRefResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIngredientRef extends ViewRecord
{
    protected static string $resource = IngredientRefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
