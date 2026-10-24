<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LabIngredientStatus: string implements HasColor, HasLabel
{
    case NotSubmitted = 'not_submitted';
    case AwaitingReview = 'awaiting_review';
    case InformationRequested = 'information_requested';
    case Refused = 'refused';
    case Approved = 'approved';
    case ChangedSinceApproval = 'changed_since_approval';
    case ApprovalExpired = 'approval_expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::NotSubmitted => 'À soumettre',
            self::AwaitingReview => 'En attente de revue',
            self::InformationRequested => 'Information demandée',
            self::Refused => 'Refusé',
            self::Approved => 'Approuvé',
            self::ChangedSinceApproval => "Modifié depuis l'approbation",
            self::ApprovalExpired => 'Approbation expirée',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NotSubmitted => 'gray',
            self::AwaitingReview, self::InformationRequested => 'warning',
            self::Refused, self::ChangedSinceApproval, self::ApprovalExpired => 'danger',
            self::Approved => 'success',
        };
    }
}
