<?php

namespace App\Filament\Resources\IngredientRefs\Pages;

use App\Filament\Resources\IngredientRefs\IngredientRefResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIngredientRefs extends ListRecords
{
    protected static string $resource = IngredientRefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
