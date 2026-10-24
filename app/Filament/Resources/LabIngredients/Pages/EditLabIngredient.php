<?php

namespace App\Filament\Resources\LabIngredients\Pages;

use App\Enums\LabIngredientStatus;
use App\Filament\Resources\LabIngredients\LabIngredientResource;
use App\Models\LabIngredient;
use App\Models\User;
use App\Services\IngredientReviewSubmissionService;
use App\Services\LabIngredientStatusService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLabIngredient extends EditRecord
{
    protected static string $resource = LabIngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitForReview')
                ->label('Soumettre pour revue')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalDescription('Les informations et documents actuels seront figés pour permettre leur contrôle.')
                ->visible(function (LabIngredientStatusService $statusService): bool {
                    if (! $this->record instanceof LabIngredient) {
                        return false;
                    }

                    return in_array($statusService->status($this->record), [
                        LabIngredientStatus::NotSubmitted,
                        LabIngredientStatus::InformationRequested,
                        LabIngredientStatus::Refused,
                        LabIngredientStatus::ChangedSinceApproval,
                        LabIngredientStatus::ApprovalExpired,
                    ], true);
                })
                ->action(function (IngredientReviewSubmissionService $submissionService): void {
                    $user = Filament::auth()->user();

                    if (! $this->record instanceof LabIngredient || ! $user instanceof User) {
                        return;
                    }

                    $submissionService->submit($this->record, $user);
                    $this->record->refresh();

                    Notification::make()
                        ->title('Ingrédient soumis pour revue')
                        ->success()
                        ->send();
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
