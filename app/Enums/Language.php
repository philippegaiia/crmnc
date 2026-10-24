<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Language: string implements HasLabel
{
    case French = 'fr';
    case Spanish = 'es';
    case English = 'en';
    case Italian = 'it';

    public function getLabel(): string
    {
        return match ($this) {
            self::French => 'Français',
            self::Spanish => 'Espagnol',
            self::English => 'Anglais',
            self::Italian => 'Italien',
        };
    }
}
