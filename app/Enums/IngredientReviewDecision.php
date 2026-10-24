<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IngredientReviewDecision: string implements HasColor, HasLabel
{
    case Approved = 'approuve';
    case Refused = 'refuse';

    public function getLabel(): string
    {
        return match ($this) {
            self::Approved => 'Approuvé',
            self::Refused => 'Refusé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Refused => 'danger',
        };
    }
}
