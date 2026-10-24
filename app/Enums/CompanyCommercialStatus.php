<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CompanyCommercialStatus: string implements HasColor, HasLabel
{
    case DemandeInfo = 'demande_info';
    case Prospect = 'prospect';
    case DossierEnvoye = 'dossier_envoye';
    case Relance = 'relance';
    case Converti = 'converti';
    case SansSuite = 'sans_suite';

    public function getLabel(): string
    {
        return match ($this) {
            self::DemandeInfo => "Demande d'information",
            self::Prospect => 'Prospect qualifié',
            self::DossierEnvoye => 'Dossier envoyé',
            self::Relance => 'Relance',
            self::Converti => 'Converti',
            self::SansSuite => 'Sans suite',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DemandeInfo => 'gray',
            self::Prospect, self::DossierEnvoye, self::Relance => 'warning',
            self::Converti => 'success',
            self::SansSuite => 'danger',
        };
    }
}
