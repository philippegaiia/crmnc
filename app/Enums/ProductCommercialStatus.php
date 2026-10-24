<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductCommercialStatus: string implements HasColor, HasLabel
{
    case Brouillon = 'brouillon';
    case Commercialise = 'commercialise';
    case Suspendu = 'suspendu';
    case Retire = 'retire';

    public function getLabel(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon', self::Commercialise => 'Commercialisé', self::Suspendu => 'Suspendu', self::Retire => 'Retiré'
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Brouillon => 'gray', self::Commercialise => 'success', self::Suspendu => 'warning', self::Retire => 'danger'
        };
    }
}
