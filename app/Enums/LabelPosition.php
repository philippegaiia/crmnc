<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LabelPosition: string implements HasColor, HasLabel
{
    case Autorise = 'autorise';
    case Interdit = 'interdit';
    case SousConditions = 'sous_conditions';
    case AStatuer = 'a_statuer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Autorise => 'Autorisé', self::Interdit => 'Interdit',
            self::SousConditions => 'Sous conditions', self::AStatuer => 'À statuer',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Autorise => 'success', self::Interdit => 'danger',
            self::SousConditions => 'warning', self::AStatuer => 'gray',
        };
    }
}
