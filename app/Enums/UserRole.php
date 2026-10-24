<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Gestionnaire = 'gestionnaire';
    case Evaluateur = 'evaluateur';
    case Adherent = 'adherent';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Gestionnaire => 'Gestionnaire association',
            self::Evaluateur => 'Évaluateur scientifique',
            self::Adherent => 'Adhérent',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Gestionnaire => 'warning',
            self::Evaluateur => 'info',
            self::Adherent => 'success',
        };
    }

    public function isAssociationManager(): bool
    {
        return in_array($this, [self::Admin, self::Gestionnaire], true);
    }
}
