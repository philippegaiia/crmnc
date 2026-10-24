<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IngredientOrigin: string implements HasLabel
{
    case Vegetale = 'vegetale';
    case Minerale = 'minerale';
    case Animale = 'animale';
    case Synthese = 'synthese';
    case Mixte = 'mixte';
    case Autre = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::Vegetale => 'Végétale', self::Minerale => 'Minérale', self::Animale => 'Animale',
            self::Synthese => 'Synthèse', self::Mixte => 'Mixte', self::Autre => 'Autre',
        };
    }
}
