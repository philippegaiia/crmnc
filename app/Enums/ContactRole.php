<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactRole: string implements HasLabel
{
    case Dirigeant = 'dirigeant';
    case Qualite = 'qualite';
    case Formulation = 'formulation';
    case Commercial = 'commercial';
    case Administratif = 'administratif';
    case Autre = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dirigeant => 'Dirigeant',
            self::Qualite => 'Qualité',
            self::Formulation => 'Formulation',
            self::Commercial => 'Commercial',
            self::Administratif => 'Administratif',
            self::Autre => 'Autre',
        };
    }
}
