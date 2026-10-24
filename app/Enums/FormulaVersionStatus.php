<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FormulaVersionStatus: string implements HasColor, HasLabel
{
    case Brouillon = 'brouillon';
    case Soumis = 'soumis';
    case Incomplet = 'incomplet';
    case EnEtude = 'en_etude';
    case AttenteInfo = 'attente_info';
    case Accepte = 'accepte';
    case Refuse = 'refuse';
    case Remplace = 'remplace';

    public function getLabel(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon', self::Soumis => 'Soumise', self::Incomplet => 'Incomplète',
            self::EnEtude => 'En étude', self::AttenteInfo => "En attente d'information",
            self::Accepte => 'Acceptée', self::Refuse => 'Refusée', self::Remplace => 'Remplacée',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Brouillon => 'gray', self::Soumis, self::EnEtude => 'info',
            self::Incomplet, self::AttenteInfo => 'warning', self::Accepte => 'success',
            self::Refuse => 'danger', self::Remplace => 'gray',
        };
    }
}
