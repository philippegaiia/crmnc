<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IngredientRefStatus: string implements HasColor, HasLabel
{
    case AValider = 'a_valider';
    case Valide = 'valide';
    case Archive = 'archive';

    public function getLabel(): string
    {
        return match ($this) {
            self::AValider => 'À valider', self::Valide => 'Validé', self::Archive => 'Archivé'
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AValider => 'warning', self::Valide => 'success', self::Archive => 'gray'
        };
    }
}
