<?php

namespace App\Filament\Resources\Formulas\Pages;

use App\Filament\Resources\Formulas\FormulaResource;
use App\Filament\Resources\FormulaVersions\FormulaVersionResource;
use App\Models\Formula;
use App\Models\User;
use App\Services\FormulaVersionCreationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFormula extends EditRecord
{
    protected static string $resource = FormulaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newVersion')
                ->label('Nouvelle version')
                ->icon('heroicon-o-document-duplicate')
                ->requiresConfirmation()
                ->action(function (): void {
                    $user = Filament::auth()->user();

                    if (! $this->record instanceof Formula || ! $user instanceof User) {
                        return;
                    }

                    $version = app(FormulaVersionCreationService::class)->createDraft($this->record, $user);

                    Notification::make()->title('Nouvelle version créée')->success()->send();
                    $this->redirect(FormulaVersionResource::getUrl('edit', ['record' => $version]));
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
