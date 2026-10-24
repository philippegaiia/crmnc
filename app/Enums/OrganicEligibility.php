<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrganicEligibility: string implements HasLabel
{
    case Oui = 'oui';
    case Non = 'non';
    case Conditionnel = 'conditionnel';
    case Inconnu = 'inconnu';

    public function getLabel(): string
    {
        return match ($this) {
            self::Oui => 'Oui', self::Non => 'Non', self::Conditionnel => 'Conditionnel', self::Inconnu => 'Inconnu',
        };
    }
}
