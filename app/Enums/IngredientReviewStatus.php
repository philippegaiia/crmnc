<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IngredientReviewStatus: string implements HasColor, HasLabel
{
    case Submitted = 'soumise';
    case UnderReview = 'en_revue';
    case InformationRequested = 'info_demandee';
    case Decided = 'decidee';

    public function getLabel(): string
    {
        return match ($this) {
            self::Submitted => 'Soumise',
            self::UnderReview => 'En revue',
            self::InformationRequested => 'Informations demandées',
            self::Decided => 'Décidée',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Submitted => 'info',
            self::UnderReview => 'warning',
            self::InformationRequested => 'danger',
            self::Decided => 'success',
        };
    }

    public function isPending(): bool
    {
        return in_array($this, [self::Submitted, self::UnderReview], true);
    }
}
