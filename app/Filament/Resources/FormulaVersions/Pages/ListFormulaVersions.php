<?php

namespace App\Filament\Resources\FormulaVersions\Pages;

use App\Filament\Resources\FormulaVersions\FormulaVersionResource;
use Filament\Resources\Pages\ListRecords;

class ListFormulaVersions extends ListRecords
{
    protected static string $resource = FormulaVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
