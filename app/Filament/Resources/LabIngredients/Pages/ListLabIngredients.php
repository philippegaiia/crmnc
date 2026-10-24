<?php

namespace App\Filament\Resources\LabIngredients\Pages;

use App\Filament\Resources\LabIngredients\LabIngredientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLabIngredients extends ListRecords
{
    protected static string $resource = LabIngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
