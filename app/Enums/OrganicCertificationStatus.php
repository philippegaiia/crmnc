<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrganicCertificationStatus: string implements HasColor, HasLabel
{
    case Certified = 'bio_certifie';
    case Conventional = 'conventionnel';
    case Partial = 'partiellement_bio';
    case Unknown = 'inconnu';

    public function getLabel(): string
    {
        return match ($this) {
            self::Certified => 'Bio certifié',
            self::Conventional => 'Conventionnel / non bio',
            self::Partial => 'Partiellement bio',
            self::Unknown => 'À déterminer',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Certified => 'success',
            self::Conventional => 'gray',
            self::Partial => 'warning',
            self::Unknown => 'info',
        };
    }
}
