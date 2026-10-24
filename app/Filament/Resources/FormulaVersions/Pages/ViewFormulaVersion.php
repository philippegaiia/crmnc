<?php

namespace App\Filament\Resources\FormulaVersions\Pages;

use App\Filament\Resources\FormulaVersions\FormulaVersionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFormulaVersion extends ViewRecord
{
    protected static string $resource = FormulaVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
