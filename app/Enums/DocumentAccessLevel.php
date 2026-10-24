<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentAccessLevel: string implements HasLabel
{
    case Internal = 'interne';
    case Committee = 'comite';
    case Adherent = 'adherent';

    public function getLabel(): string
    {
        return match ($this) {
            self::Internal => 'Interne',
            self::Committee => 'Comité',
            self::Adherent => 'Adhérent concerné',
        };
    }
}
