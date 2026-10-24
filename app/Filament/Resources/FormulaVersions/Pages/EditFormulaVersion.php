<?php

namespace App\Filament\Resources\FormulaVersions\Pages;

use App\Filament\Resources\FormulaVersions\FormulaVersionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFormulaVersion extends EditRecord
{
    protected static string $resource = FormulaVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
