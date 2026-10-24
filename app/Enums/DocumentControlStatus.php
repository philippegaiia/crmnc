<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DocumentControlStatus: string implements HasColor, HasLabel
{
    case Received = 'recu';
    case ToVerify = 'a_verifier';
    case Validated = 'valide';
    case Refused = 'refuse';
    case Expired = 'expire';

    public function getLabel(): string
    {
        return match ($this) {
            self::Received => 'Reçu',
            self::ToVerify => 'À vérifier',
            self::Validated => 'Validé',
            self::Refused => 'Refusé',
            self::Expired => 'Expiré',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Received => 'gray',
            self::ToVerify => 'warning',
            self::Validated => 'success',
            self::Refused, self::Expired => 'danger',
        };
    }
}
