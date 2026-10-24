<?php

namespace App\Filament\Resources\Formulas\Pages;

use App\Filament\Resources\Formulas\FormulaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFormula extends ViewRecord
{
    protected static string $resource = FormulaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
