<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentType: string implements HasLabel
{
    case TechnicalDossier = 'dossier_technique';
    case TechnicalSheet = 'fiche_technique';
    case Certificate = 'certificat';
    case Attestation = 'attestation';
    case MarketingKit = 'kit_marketing';
    case Correspondence = 'echange';
    case Other = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::TechnicalDossier => 'Dossier technique',
            self::TechnicalSheet => 'Fiche technique',
            self::Certificate => 'Certificat',
            self::Attestation => 'Attestation',
            self::MarketingKit => 'Kit marketing',
            self::Correspondence => 'Échange',
            self::Other => 'Autre',
        };
    }
}
