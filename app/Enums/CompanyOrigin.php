<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CompanyOrigin: string implements HasLabel
{
    case LinkedIn = 'linkedin';
    case Salon = 'salon';
    case BoucheAOreille = 'bouche_a_oreille';
    case SiteWeb = 'site_web';
    case Presse = 'presse';
    case Recommandation = 'recommandation';
    case Autre = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::LinkedIn => 'LinkedIn',
            self::Salon => 'Salon',
            self::BoucheAOreille => 'Bouche-à-oreille',
            self::SiteWeb => 'Site web',
            self::Presse => 'Presse',
            self::Recommandation => 'Recommandation',
            self::Autre => 'Autre',
        };
    }
}
