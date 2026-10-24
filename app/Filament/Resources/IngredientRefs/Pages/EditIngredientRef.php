<?php

namespace App\Filament\Resources\IngredientRefs\Pages;

use App\Filament\Resources\IngredientRefs\IngredientRefResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditIngredientRef extends EditRecord
{
    protected static string $resource = IngredientRefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
