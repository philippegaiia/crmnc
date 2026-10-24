<?php

namespace App\Filament\Resources\Formulas\Pages;

use App\Filament\Resources\Formulas\FormulaResource;
use App\Models\Formula;
use App\Models\User;
use App\Services\FormulaVersionCreationService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFormula extends CreateRecord
{
    protected static string $resource = FormulaResource::class;

    protected function afterCreate(): void
    {
        $user = Filament::auth()->user();

        if ($this->record instanceof Formula && $user instanceof User) {
            app(FormulaVersionCreationService::class)->createDraft($this->record, $user);
        }
    }
}
